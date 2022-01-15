<?php

declare(strict_types=1);

namespace Baraja\Shop\Price;


use Baraja\EcommerceStandard\DTO\PriceInterface;

interface PriceRendererInterface
{
	public function render(
		PriceInterface|float|string $price,
		?string $locale = null,
		?string $expectedCurrency = null,
		?string $currentCurrency = null,
	): string;
}
