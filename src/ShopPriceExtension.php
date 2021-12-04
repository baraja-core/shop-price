<?php

declare(strict_types=1);

namespace Baraja\Shop\Price;


use Nette\DI\CompilerExtension;

final class ShopPriceExtension extends CompilerExtension
{
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('priceRenderer'))
			->setFactory(PriceRenderer::class);
	}
}
