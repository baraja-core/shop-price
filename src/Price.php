<?php

declare(strict_types=1);

namespace Baraja\Shop\Price;


use Baraja\EcommerceStandard\DTO\CurrencyInterface;
use Baraja\EcommerceStandard\DTO\PriceInterface;

class Price implements PriceInterface
{
	/** @var numeric-string */
	private string $value;


	public function __construct(
		string|int|float|PriceInterface $value,
		private CurrencyInterface $currency,
	) {
		if ($value instanceof PriceInterface) {
			if ($value->getCurrency()->getCode() !== $currency->getCode()) {
				throw new \InvalidArgumentException(
					sprintf('Given price value is not compatible, because different currencies given.')
				);
			}
			$value = $value->getValue();
		} elseif (is_string($value)) {
			$value = self::normalize($value);
		} else {
			$value = (string) $value;
		}
		$this->value = $value;
	}


	/**
	 * @param numeric-string $value
	 * @return numeric-string
	 */
	public static function normalize(string $value, int $precision = 2): string
	{
		$value = $value === '' ? '0' : $value;
		$parts = explode('.', $value, $precision);
		$left = ltrim($parts[0] ?? '', '0');
		$right = rtrim(substr($parts[1] ?? '', 0, 2), '0');

		return $left . ($right !== '' ? '.' . $right : '');
	}


	public function __toString(): string
	{
		return $this->render();
	}


	public function render(bool $html = false): string
	{
		return $this->currency->renderPrice($this->getValue(), $html);
	}


	/** @return numeric-string */
	public function getValue(): string
	{
		return $this->value;
	}


	public function getCurrency(): CurrencyInterface
	{
		return $this->currency;
	}


	public function isFree(): bool
	{
		return $this->value === '0';
	}


	public function getDiff(PriceInterface|string $price): string
	{
		if ($price instanceof PriceInterface) {
			$this->checkCurrency($price);
			$value = $price->getValue();
		} else {
			$value = $price;
		}

		return bcsub($this->value, $value, 2);
	}


	public function isBiggerThan(PriceInterface|string $price): bool
	{
		return $this->getDiff($price) > 0.01;
	}


	public function isSmallerThan(PriceInterface|string $price): bool
	{
		return $this->getDiff($price) < 0.01;
	}


	public function isEqualTo(PriceInterface|string $price): bool
	{
		return $this->getDiff($price) < 0.0001;
	}


	public function plus(PriceInterface $price): PriceInterface
	{
		$this->checkCurrency($price);

		return new self(bcadd($this->value, $price->getValue(), 2), $price->getCurrency());
	}


	public function minus(PriceInterface $price): PriceInterface
	{
		$this->checkCurrency($price);

		return new self(bcsub($this->value, $price->getValue(), 2), $price->getCurrency());
	}


	private function checkCurrency(PriceInterface $price): void
	{
		if ($price->getCurrency()->getCode() !== $this->currency->getCode()) {
			throw new \InvalidArgumentException(
				sprintf('Given price value is not compatible, because different currencies given.')
			);
		}
	}
}
