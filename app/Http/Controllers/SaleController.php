<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportSalesRequest;
use App\Http\Requests\SaleRequest;
use App\Models\Client;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SaleController extends Controller
{
    // GET /sales
    public function index(Request $request)
    {
        $query = Sale::query()
            ->with(['user:id,name', 'client:id,name']) // ajuste campos de Client
            ->when($request->filled('seller'), fn($q) => $q->where('user_id', $request->integer('seller')))
            ->when($request->filled('status'), fn($q) => $q->where('payment_status', $request->string('status')))
            ->when($request->filled('from'), fn($q) => $q->whereDate('sale_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn($q) => $q->whereDate('sale_date', '<=', $request->date('to')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->string('search');
                $q->where(function ($qq) use ($s) {
                    $qq->where('name', 'like', "%{$s}%")
                       ->orWhere('cpf', 'like', "%{$s}%")
                       ->orWhere('proposal_number', 'like', "%{$s}%")
                       ->orWhere('product', 'like', "%{$s}%")
                       ->orWhere('bank', 'like', "%{$s}%");
                });
            })
            ->orderByDesc('sale_date')
            ->orderByDesc('id');

        $sales = $query->paginate(15)->withQueryString();

        // Auxiliares para filtros
        $sellers = User::query()->select('id','name')->orderBy('name')->get();
        $statuses = ['pending','paid','canceled'];

        return Inertia::render('Sales/Index', [
            'sales'   => $sales,
            'filters' => [
                'seller' => $request->input('seller'),
                'status' => $request->input('status'),
                'from'   => $request->input('from'),
                'to'     => $request->input('to'),
                'search' => $request->input('search'),
            ],
            'sellers'  => $sellers,
            'statuses' => $statuses,
        ]);
    }

    public function create()
    {
        return Inertia::render('Sales/Create', [
            'sellers' => User::select('id','name')->orderBy('name')->get(),
            'clients' => [],
            'defaults' => [
                'payment_status' => 'pending',
                'sale_date'      => now()->toDateString(),
            ],
        ]);
    }

    public function store(SaleRequest $request)
    {
        $data = $request->validated();

        $data['user_id'] = Auth::id();

        if (!empty($data['client_id'])) {
            if ($client = Client::select('name','cpf')->find($data['client_id'])) {
                $data['name'] = $client->name;
                $data['cpf']  = $client->cpf;
            }
        }

        $amount = (float) str_replace(',', '.', (string) ($data['amount'] ?? 0));
        $pct    = (float) str_replace(',', '.', (string) ($data['commission_percentage'] ?? 0));

        $data['amount'] = $amount;
        $data['commission_percentage'] = $pct;

        $data['commission_value'] = round($amount * ($pct / 100), 2);

        if (($data['payment_status'] ?? 'pending') !== 'paid') {
            $data['paid_at'] = null;
        }

        $sale = Sale::create($data);

        return redirect()->route('sales.index')
            ->with('success', 'Venda criada com sucesso.');
    }

    public function import(ImportSalesRequest $request)
    {
        $file = $request->file('file');

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows)) {
            return back()->with('error', 'Arquivo vazio.');
        }

        $headerMap = [
            'NOME_CORRETOR'        => 'seller_name',
            'NOM_CLIENTE'          => 'name',
            'COD_CPF_CLIENTE'      => 'cpf',
            'NUM_PROPOSTA'         => 'proposal_number',
            'VALOR PROPOSTA'       => 'amount',
            'DSC_PRODUTO'          => 'product',
            'NOM_BANCO'            => 'bank',
            'PERCENTUAL COMISSÃO'  => 'commission_percentage',
            'COMISSÃO RECEBIDA'    => 'commission_received', // opcional p/ usar como commission_value
            'DAT_EMPRESTIMO'       => 'sale_date',
            // 'NUM_BANCO'          => 'bank_code', // se quiser usar
        ];

        // Índices das colunas
        $headerRow = array_shift($rows);
        $colIndex = [];
        foreach ($headerRow as $colLetter => $text) {
            $text = trim((string) $text);
            if (isset($headerMap[$text])) {
                $colIndex[$headerMap[$text]] = $colLetter;
            }
        }

        foreach ([
            'seller_name','name','cpf','proposal_number','amount',
            'product','bank','commission_percentage','sale_date'
        ] as $needed) {
            if (!isset($colIndex[$needed])) {
                return back()->with('error', "Coluna obrigatória ausente no XLSX: {$needed}");
            }
        }

        $created = 0;
        $failed  = 0;
        $errors  = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $i => $r) {
                // Linha em branco?
                if (!trim((string)($r[$colIndex['seller_name']] ?? '')) &&
                    !trim((string)($r[$colIndex['name']] ?? ''))) {
                    continue;
                }

                try {
                    $sellerName = trim((string) ($r[$colIndex['seller_name']] ?? ''));
                    $userId = User::whereRaw('LOWER(name) = ?', [mb_strtolower($sellerName)])->value('id');
                    if (!$userId) {
                        throw new \RuntimeException("Vendedor não encontrado: {$sellerName}");
                    }

                    // Resolve cliente -> client_id (por nome; se não achar, null)
                    $clientName = trim((string) ($r[$colIndex['name']] ?? ''));
                    $clientId = Client::whereRaw('LOWER(name) = ?', [mb_strtolower($clientName)])->value('id');

                    // Normalizações numéricas
                    $amount = (float) str_replace(['.', ','], ['', '.'], (string) ($r[$colIndex['amount']] ?? 0));
                    $pct    = (float) str_replace(['.', ','], ['', '.'], (string) ($r[$colIndex['commission_percentage']] ?? 0));

                    // Comissão recebida (se vier na planilha, priorize; senão calcula)
                    $commissionReceived = isset($colIndex['commission_received'])
                        ? (float) str_replace(['.', ','], ['', '.'], (string) ($r[$colIndex['commission_received']] ?? 0))
                        : null;

                    $commissionValue = $commissionReceived !== null && $commissionReceived > 0
                        ? round($commissionReceived, 2)
                        : round($amount * ($pct / 100), 2);

                    // Derivar status: se houver comissão recebida > 0 => 'paid', senão 'pending'
                    $status = ($commissionReceived !== null && $commissionReceived > 0) ? 'paid' : 'pending';

                    // Datas
                    $saleDateCell = $r[$colIndex['sale_date']] ?? null;
                    $saleDate = self::parseExcelDate($saleDateCell)?->toDateString();

                    // paid_at não existe no arquivo; deixe null (ou defina regra própria)
                    $paidAt = null;

                    // Monta dados para criação
                    $data = [
                        'user_id'               => $userId,
                        'client_id'             => $clientId,            // null se não encontrou
                        'name'                  => $clientName,          // mantém nome da planilha
                        'cpf'                   => preg_replace('/\D+/', '', (string) ($r[$colIndex['cpf']] ?? '')),
                        'proposal_number'       => trim((string) ($r[$colIndex['proposal_number']] ?? '')),
                        'amount'                => $amount,
                        'product'               => trim((string) ($r[$colIndex['product']] ?? '')),
                        'bank'                  => trim((string) ($r[$colIndex['bank']] ?? '')),
                        'commission_percentage' => $pct,
                        'commission_value'      => $commissionValue,
                        'payment_status'        => $status,              // derivado
                        'sale_date'             => $saleDate ?? now()->toDateString(),
                        'paid_at'               => $paidAt,              // permanece null
                    ];

                    Sale::create($data);
                    $created++;
                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = "Linha ".($i+2).": ".$e->getMessage(); // +2 por conta do cabeçalho
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Falha ao importar: '.$e->getMessage());
        }

        $msg = "Importação concluída. Criadas: {$created}".($failed ? " | Falhas: {$failed}" : '');
        return back()->with($failed ? 'warning' : 'success', $msg)->with('details', $errors);
    }

    /**
     * Converte célula (string ou serial Excel) para Carbon
     */
    private static function parseExcelDate($cell): ?Carbon
    {
        if ($cell === null || $cell === '') return null;

        // número → data serial do Excel
        if (is_numeric($cell)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cell));
            } catch (\Throwable) {
                return null;
            }
        }

        // string data
        try {
            return Carbon::parse((string) $cell);
        } catch (\Throwable) {
            return null;
        }
    }    
}
