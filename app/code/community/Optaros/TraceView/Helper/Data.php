<?php
/*
 * @copyright   Copyright (c) 2013 Optaros
 *
 * TraceView helper functions dealing with config and adding layers to TraceLytics
 *
 * We're coding this class with static members since it's mostly going to be
 * accessed from lib/Varien/Profiler.php where we (theoretically) don't use the 
 * Magento factory and we're not going to have a helper object, but we're going
 * to use static access instead (Optaros_TraceView_Helper_Data::addLayer() instead
 * of Mage::helper('traceview')->addLayer())
 */


class Optaros_TraceView_Helper_Data
{
	/* config xpath to enable/disable traceview monitoring */
	const XML_PATH_CONFIG_ENABLED = 'global/traceview/enabled';

	/* config xpath for timer layers */
	const XML_PATH_CONFIG_LAYERS = 'global/traceview/layers';

	/* config xpath for use_rum */
	const XML_PATH_CONFIG_USE_RUM = 'global/traceview/use_rum';

	
	/* "cached" enabled setting from config */
	static protected $_enabled = NULL;

	/* "cached" use_rum config */
	static protected $_use_rum = NULL;

	/* "cached" layers node from config */
	static protected $_layers = NULL;

	/* static layer mapping, not loaded from config */
	static protected $_staticLayers = array (
		'config' => 'mage_init',
		'system_config' => 'mage_init'
	);


	protected static function _getTimerPattern($timer) {
		return substr($timer , strrpos($timer, '::') + 2);
	}

	/**
	 * get the proper layer to log the timer into
	 *
	 * @param String $timerName timer name
	 *
	 * @return Layer name on success
	 * @return NULL if no configured layer was found
	 */
	public static function getTimerLayer($timerName) {

		if (self::$_layers === NULL) {
			self::$_layers = 
				self::_getConfigNode(self::XML_PATH_CONFIG_LAYERS, TRUE);
		}

		$pkey = self::_getTimerPattern($timerName);
		if (!empty(self::$_layers) && isset(self::$_layers[$pkey])) {
			/* layer found in config */
			return self::$_layers[$pkey];
		}

		return NULL;

	}

	/**
	 * check to see if we have a layer that we're always logging
	 *
	 * @param String $timerName timer name
	 *
	 * @return Layer name on success
	 * @return NULL if no configured layer was found
	 */
	public static function getStaticTimerLayer($timerName) {

		$pkey = self::_getTimerPattern($timerName);

		/* check the static mapping */
		if (isset(self::$_staticLayers[$pkey]))
			return self::$_staticLayers[$pkey];

		return NULL;

	}



	/**
	 * add layer in TraceLytics
	 *
	 * @param String $timerName timer name used in Varien_Profiler
	 * @param String $label usually 'entry'/'exit'
	 */
	public static function addLayer($timerName, $label) {

		$layer = self::getStaticTimerLayer($timerName);

		if ($layer !== NULL || self::isEnabled()) {

			if (empty($layer))
				$layer = self::getTimerLayer($timerName);

			if (!empty($layer)) {
				oboe_log($layer, $label, array( "timer" => $timerName));
			}

		}

	}

	/**
	 * Check whether the functionality is enabled or npt
	 *
	 * @return TRUE if it is
	 * @return FALSE otherwise
	 */
	public static function isEnabled() {

		if (self::$_enabled === NULL) {

			$cfg = self::_getConfigNode(self::XML_PATH_CONFIG_ENABLED);
			if ($cfg === NULL) {
				/* Config is not loaded at this point, simply return
				 * based on whether the oboe API is available 
				 * but DON'T cache the result, leave it NULL
				 * instead, to recompute it once the config is loaded.
				 */
				return (extension_loaded('oboe') && function_exists('oboe_log'));
			}

			self::$_enabled = 
				(
					($cfg === '1') 
				 && extension_loaded('oboe') 
				 && function_exists('oboe_log')
				);

		}

		return self::$_enabled;
	}

	/**
	 * Check whether using rum is enabled or not
	 *
	 * @return TRUE if it is
	 * @return FALSE otherwise
	 */
	public static function isRumEnabled() {
		if (self::$_use_rum === NULL) {
			self::$_use_rum = 
				self::_getConfigNode(self::XML_PATH_CONFIG_USE_RUM);
		}
		return (self::$_use_rum === '1');
	}


	protected static function _getConfigNode($xpath, $asArray = FALSE) {

		$config = Mage::getConfig();
		if (empty($config)) {
			return NULL;
		}
		
		$node = $config->getNode($xpath);
		if (empty($node)) 
			return NULL;

		return ($asArray?$node->asCanonicalArray():(string)$node);
	}

}

/* vim: set ts=4 sw=4 noexpandtab: */
