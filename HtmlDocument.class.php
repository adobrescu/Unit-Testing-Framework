<?php

class HtmlDocument extends DOMDocument
{
        public function __construct($htmlSource)
        {
                parent::__construct('1.0', 'utf-8');

                $this->loadHTML($htmlSource);
        }
        public function loadHTML($htmlSource, $options=null)
        {
                libxml_use_internal_errors(true);

                parent::loadHTML($htmlSource);

                foreach (libxml_get_errors() as $error)
                {
                        switch ($error->code)
                        {
                                case 801:
                                        continue;
                                case 42:
                                        continue;
                                default:
                                        break;
                        }

                }
                libxml_clear_errors();
        }
}
