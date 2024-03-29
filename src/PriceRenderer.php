<?php

declare(strict_types=1);

namespace Baraja\Shop\Price;


use Baraja\EcommerceStandard\DTO\PriceInterface;
use Baraja\EcommerceStandard\Service\CurrencyManagerInterface;
use Baraja\EcommerceStandard\Service\CurrencyResolverInterface;
use Baraja\EcommerceStandard\Service\ExchangeRateConvertorInterface;
use Baraja\EcommerceStandard\Service\PriceRendererInterface;
use Baraja\Localization\Localization;

final class PriceRenderer implements PriceRendererInterface
{
	public function __construct(
		private Localization $localization,
		private ExchangeRateConvertorInterface $exchangeRateConvertor,
		private CurrencyManagerInterface $currencyManager,
		private ?CurrencyResolverInterface $currencyResolver = null,
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
		if ($this->currencyResolver === null) {
			throw new \LogicException(sprintf(
				'Currency resolver (implementing "%s") not found.',
				CurrencyResolverInterface::class,
			));
		}
		if ($price instanceof PriceInterface) {
			$value = $price->getValue();
			if ($source === null) {
				$source = $price->getCurrency()->getCode();
			}
		} elseif (is_string($price)) {
			$value = $price;
		} else {
			trigger_error('Float price is deprecated. Please use numeric-string instead.', \E_USER_DEPRECATED);
			$value = (string) $price;
		}
		$locale ??= $this->localization->getLocale();
		$target ??= $this->currencyResolver->resolveCode($locale);
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
