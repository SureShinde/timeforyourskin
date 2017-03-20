<?php
class simpleGettext extends Twig_Extension {
    
    /**
     * List of allowed functions to be used in the templates
     * @var array
     */
    private $functions = array(
        '__',
        '_e',
        'esc_attr__',
        'esc_html__',
        'esc_attr_e',
        'esc_html_e',
        '_x',
        '_ex',
        'esc_attr_x',
        'esc_html_x',
        '_n',
        '_nx',
        '_n_noop',
        '_nx_noop',
        'sprintf',
    );
    
    /**
     * List of allowed functions to be used in the templates
     * @var array
     */
    private $mmReviewContainer;
    
    public function __construct(array $functions = array()) {
        if ($functions) {
            $this->allowFunctions($functions);
        }
    }
    public function getFunctions() {
        $twigFunctions = array();
        foreach ($this->functions as $function) {
            
            $twigFunctions[] = is_array($function) ? new Twig_SimpleFunction($function[1], $function) : new Twig_SimpleFunction($function, $function);
        }
        return $twigFunctions;
    }
    public function allowFunction($function) {
        $this->functions[] = $function;
    }
    public function allowFunctions(array $functions) {
        $this->functions = $functions;
    }
    public function getName() {
        return 'php_function';
    }
}
?>