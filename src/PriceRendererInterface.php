<?php

declare(strict_types=1);

namespace Baraja\Shop\Price;


interface PriceRendererInterface
{
	public function render(
		float|string $price,
		?string $locale = null,
		?string $expectedCurrency = null,
		?string $currentCurrency = null
	): string;
}