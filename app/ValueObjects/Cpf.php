<?php

namespace App\ValueObjects;

use Illuminate\Support\Str;

class Cpf
{
    private string $cpf;

    public function __construct(string $cpf)
    {
        $this->cpf = $this->removeMask($cpf);

        //fill with zeros
        $this->cpf = str_pad($this->cpf, 11, '0', STR_PAD_LEFT);
    }

    public function __toString(): string
    {
        return $this->cpf;
    }

    public function isValid(): bool
    {

        if (preg_match('/(\d)\1{10}/', $this->cpf)) {
            return false;
        }

        for ($i = 9; $i < 11; $i++) {
            for ($j = 0, $k = 0; $k < $i; $k++) {
                $j += $this->cpf[$k] * (($i + 1) - $k);
            }

            $j = ((10 * $j) % 11) % 10;

            if ($this->cpf[$k] != $j) {
                return false;
            }
        }

        return true;
    }

    private function removeMask(string $cpf): string
    {
        return Str::of($cpf)->replaceMatches('/[^0-9]/', '')->__toString();
    }

    public function format(): string
    {
        return Str::of($this->cpf)->replaceMatches('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4')->__toString();
    }

    public function getNumber(): string
    {
        return $this->cpf;
    }

    public static function fromString(string $cpf): self
    {
        return new self($cpf);
    }
}