<?php

//\Magento\Framework\Component\ComponentRegistrar::register(
//		\Magento\Framework\Component\ComponentRegistrar::MODULE,
//		'Boraso_Helloworld',
//		__DIR__
//	);

use \Magento\Framework\Component\ComponentRegistrar;
ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Boraso_CatalogRulesListFix', __DIR__);