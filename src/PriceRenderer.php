<?php

declare(strict_types=1);

namespace Baraja\Shop\Price;


use Baraja\EcommerceStandard\DTO\PriceInterface;
use Baraja\EcommerceStandard\Service\CurrencyManagerInterface;
use Baraja\EcommerceStandard\Service\ExchangeRateConvertorInterface;
use Baraja\EcommerceStandard\Service\PriceRendererInterface;
use Baraja\Localization\Localization;
use Baraja\Shop\Context;

final class PriceRenderer implements PriceRendererInterface
{
	public function __construct(
		private Localization $localization,
		private ExchangeRateConvertorInterface $exchangeRateConvertor,
		private CurrencyManagerInterface $currencyManager,
		private Context $context,
	) {
	}


	/**
	 * @param PriceInterface|float|numeric-string $price
	 */
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
			$source = $this->currencyManager->getMainCurrency()->getCode();
		}
		$converted = new Price(
			value: $this->exchangeRateConvertor->convert($value, $target, $source),
			currency: $this->currencyManager->getCurrency($source),
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
