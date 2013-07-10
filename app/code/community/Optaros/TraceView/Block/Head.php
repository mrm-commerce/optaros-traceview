<?php
// Copyright (C) 2013 Optaros, Inc. All rights reserved.
 
class Optaros_TraceView_Block_Head extends Mage_Core_Block_Text {
 
	/**
	 * Render the RUM JS header
	 */
	protected function _toHtml() {
		if (Optaros_TraceView_Helper_Data::isEnabled() 
			&& Optaros_TraceView_Helper_Data::isRumEnabled()) {
			$this->addText(oboe_get_rum_header());
	    }
	    return parent::_toHtml();
    }

	/**
	 * Method used to render the block in the applyWithoutApp()
	 * FPC method
	 */
	public function renderWithoutApp() {
		return $this->_toHtml();
	}
}

/* vim: set ts=4 sw=4 noexpandtab: */
