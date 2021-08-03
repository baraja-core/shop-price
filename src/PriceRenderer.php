<?php

declare(strict_types=1);

namespace Baraja\Shop\Price;


use Baraja\CurrencyExchangeRate\CurrencyExchangeRateManager;
use Baraja\Localization\Localization;
use Nette\Utils\Floats;

final class PriceRenderer implements PriceRendererInterface
{
	public const SYMBOL_MAP = [
		'EUR' => '€',
		'GBP' => '£',
		'PLN' => 'zł',
	];

	public const LOCALE_CURRENCY = [
		'cs' => 'CZK',
		'sk' => 'EUR',
		'en' => 'EUR',
		'de' => 'EUR',
	];


	public function __construct(
		private Localization $localization,
		private CurrencyExchangeRateManager $currencyManager,
		private CurrencyResolver $currencyResolver,
		private int $decimals = 2,
	) {
	}


	public function render(
		float|string $price,
		?string $locale = null,
		?string $expectedCurrency = null,
		?string $currentCurrency = null
	): string {
		$locale ??= $this->localization->getLocale();
		$expectedCurrency = $this->currencyResolver->getCurrency($expectedCurrency, $locale);
		if ($currentCurrency === null) {
			$currentCurrency = self::LOCALE_CURRENCY[$this->localization->getDefaultLocale()]
				?? throw new \InvalidArgumentException('Base currency does not exist.');
		}

		$converted = $this->currencyManager->getPrice($price, $expectedCurrency, $currentCurrency, true);
		if (Floats::isZero($converted)) {
			return $this->getFreeLabel();
		}

		$return = number_format($converted, $this->decimals, '.', ' ');
		if (preg_match('/^(\d*)\.(\d*)$/', $return, $match) === 1) {
			$right = rtrim($match[2], '0');
			$return = ($match[1] === '' ? '0' : $match[1]) . ($right !== '' ? '.' . $right : '');
		}

		return $return . '&nbsp;' . $this->renderSymbol($locale, $expectedCurrency);
	}


	private function renderSymbol(string $locale, string $currency): string
	{
		if ($locale === 'cs' && $currency === 'CZK') {
			return 'Kč';
		}
		if (isset(self::SYMBOL_MAP[$currency])) {
			return self::SYMBOL_MAP[$currency];
		}
		if (\in_array($currency, ['USD', 'AUD', 'HKD', 'CAD', 'NZD', 'SGD'], true)) {
			return '$';
		}

		return $currency;
	}


	private function getFreeLabel(): string
	{
		return 'Zdarma';
	}
}
