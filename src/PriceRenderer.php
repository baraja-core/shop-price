<?php

declare(strict_types=1);

namespace Baraja\Shop\Price;


use Baraja\EcommerceStandard\DTO\PriceInterface;
use Baraja\EcommerceStandard\Service\PriceRendererInterface;
use Baraja\Localization\Localization;
use Baraja\Shop\Context;
use Baraja\Shop\Currency\CurrencyManagerAccessor;
use Baraja\Shop\Currency\ExchangeRateConvertor;

final class PriceRenderer implements PriceRendererInterface
{
	public function __construct(
		private Localization $localization,
		private ExchangeRateConvertor $exchangeRateConvertor,
		private CurrencyManagerAccessor $currencyManager,
		private Context $context,
	) {
	}


	public function render(
		PriceInterface|float|string $price,
		?string $locale = null,
		?string $target = null,
		?string $source = null,
	): string {
		if ($price instanceof PriceInterface) {
			$value = $price->getValue();
			if ($source === null) {
				$source = $price->getCurrency()->getCode();
			}
		} else {
			$value = (string) $price;
		}
		$locale ??= $this->localization->getLocale();
		$target ??= $this->context->getCurrencyResolver()->resolveCode($locale);
		if ($source === null) {
			$source = $this->currencyManager->get()->getMainCurrency()->getCode();
		}
		$converted = new Price(
			value: $this->exchangeRateConvertor->convert($value, $target, $source),
			currency: $this->currencyManager->get()->getCurrency($source),
		);
		if ($converted->isFree()) {
			return $this->getFreeLabel();
		}

		return $converted->render(true);
	}


	public function getFreeLabel(): string
	{
		return 'Zdarma';
	}
}
