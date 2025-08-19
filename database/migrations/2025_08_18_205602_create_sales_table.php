<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            // Relacionamentos
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            // Dados da venda
            $table->string('name'); // nome do cliente
            $table->string('cpf', 14); // CPF formatado
            $table->string('proposal_number')->unique(); // Nº proposta
            $table->decimal('amount', 12, 2); // Valor da venda
            $table->string('product'); // Produto
            $table->string('bank'); // Banco
            $table->decimal('commission_percentage', 5, 2); // % comissão
            $table->decimal('commission_value', 12, 2); // valor a receber (amount * %)
            $table->enum('payment_status', ['pending', 'paid', 'canceled'])->default('pending');

            // Datas
            $table->date('sale_date')->nullable(); // data da venda
            $table->timestamp('paid_at')->nullable(); // data de pagamento da comissão

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
