<?php

namespace Karma;

interface FormatterProvider
{
    public function hasFormatter($index);
    
    public function getFormatter($index = null);
}