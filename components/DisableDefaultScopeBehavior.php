<?php

namespace app\components;


class DisableDefaultScopeBehavior extends ActiveRecordBehavior
{
    private $_defaultScopeDisabled = false; // Flag - whether defaultScope is disabled or not

    public function disableDefaultScope()
    {
        $this->_defaultScopeDisabled = true;
        return $this->Owner;
    }

    public function getDefaultScopeDisabled()
    {
        return $this->_defaultScopeDisabled;
    }

}
