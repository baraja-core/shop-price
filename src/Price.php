<?php

declare(strict_types=1);

namespace Baraja\Shop\Price;


use Baraja\EcommerceStandard\DTO\CurrencyInterface;
use Baraja\EcommerceStandard\DTO\PriceInterface;

class Price implements PriceInterface
{
	public function __construct(
		private float $value,
		private CurrencyInterface $currency,
	) {
	}


	public function __toString(): string
	{
		return $this->render();
	}


	public function render(bool $html = false): string
	{
		return $this->currency->renderPrice($this->getValue(), $html);
	}


	public function getValue(): float
	{
		return $this->value;
	}


	public function getCurrency(): CurrencyInterface
	{
		return $this->currency;
	}


	public function isFree(): bool
	{
		return abs($this->value) < 0.0001;
	}


	public function getDiff(PriceInterface|float $price): float
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
}
