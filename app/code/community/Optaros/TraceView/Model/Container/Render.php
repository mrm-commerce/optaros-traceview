<?php
// Copyright (C) 2013 Optaros, Inc. All rights reserved.
 
class Optaros_Traceview_Model_Container_Render extends Enterprise_PageCache_Model_Container_Abstract
{

    public function applyWithoutApp(&$content)
    {
        $blockContent = $this->_renderBlock();
        if ($blockContent === false) {
            return false;
        }
        $this->_applyToContent($content, $blockContent);
        return true;
    }

    public function applyInApp(&$content)
	{
		return $this->applyWithoutApp($content);
    }

    protected function _renderBlock()
    {
		$blockName = $this->_placeholder->getAttribute('block'); 
		$block = new $blockName;
        return $block->renderWithoutApp();
    }

    protected function _getCacheId()
	{
		return false;
    }

}
