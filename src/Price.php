<?php

declare(strict_types=1);

namespace Baraja\Shop\Price;


use Baraja\Shop\Entity\Currency\Currency;

class Price implements \Stringable
{
	public function __construct(
		private float $value,
		private Currency $currency,
	) {
	}


	public function __toString(): string
	{
		return $this->formatPrice($this->getValue()) . ' ' . $this->getCurrency()->getSymbol();
	}


	public function getValue(): float
	{
		return $this->value;
	}


	public function getCurrency(): Currency
	{
		return $this->currency;
	}


	public function isFree(): bool
	{
		return abs($this->value) < 0.0001;
	}


	public function getDiff(self|float $price): float
	{
		if ($price instanceof self) {
			$value = $price->getValue();
		} else {
			$value = $price;
		}

		return $this->getValue() - $value;
	}


	public function isBigger(self|float $price): bool
	{
		return $this->getDiff($price) > 0.01;
	}


	public function isSmaller(self|float $price): bool
	{
		return $this->getDiff($price) < 0.01;
	}


	public function isEqual(self|float $price): bool
	{
		return $this->getDiff($price) < 0.0001;
	}


	private function formatPrice(float $price): string
	{
		return str_replace(',00', '', number_format($price, 2, ',', ' '));
	}
}
