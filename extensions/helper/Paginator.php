<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_paginate\extensions\helper;

/**
 * A Pagination helper extension
 *
 * A template helper that assists in generating pagination links. Accessible in templates via
 * `$this->paginator`, which will auto-load this helper into the rendering context. For examples of how
 * to use this helper, see the documentation for a specific method.
 */
class Paginator extends \lithium\template\Helper {

	/**
	 * Library string used by this helper to create next/prev & page links.
	 *
	 * This string is representative of the name of the current library,
	 * it is fetched during _init() via $this->_context.
	 *
	 * @var string
	 * @see li3_paginate\extensions\helper\Paginator::_init()
	 */
	protected $_library = null;
	
	/**
	 * Controller string used by this helper to create next/prev & page links.
	 *
	 * This string is representative of the name of the current controller,
	 * it is fetched during _init() via $this->_context.
	 *
	 * @var string
	 * @see li3_paginate\extensions\helper\Paginator::_init()
	 */
	protected $_controller = null;

	/**
	 * Action string used by this helper to create next/prev & page links.
	 *
	 * This string is representative of the action being called within the current controller,
	 * it is set during _init() via properties of $this->_context.
	 *
	 * @var string
	 * @see li3_paginate\extensions\helper\Paginator::_init()
	 */
	protected $_action = null;

	/**
	 * Page value used by this helper to create next/prev & page links.
	 *
	 * Represents the current page number,
	 * it is set during _init() via properties of $this->_context.
	 *
	 * @var integer
	 * @see li3_paginate\extensions\helper\Paginator::_init()
	 */
	protected $_page = null;

	/**
	 * Total value used by this helper to create next/prev & page links.
	 *
	 * Represents the total number of records to paginate against,
	 * it is set during _init() via properties of $this->_context.
	 *
	 * @var integer
	 * @see li3_paginate\extensions\helper\Paginator::_init()
	 */
	protected $_total = null;

	/**
	 * Limit value used by this helper to create next/prev & page links.
	 *
	 * Represents the number of records/documents to show per page,
	 * it is fetched during _init() via $this->_context.
	 *
	 * @var integer
	 * @see li3_paginate\extensions\helper\Paginator::_init()
	 */
	protected $_limit = null;

	/**
	 * Protected array of string templates used by this helper.
	 *
	 * Currently only one string template is being used to wrap the controls generated by the paginate() method.
	 *
	 * @var array
	 * @see li3_paginate\extensions\helper\Paginator::paginate()
	 */
	protected $_strings = array(
		'pagingWrapper'	=> '<ul>{:content}</ul>'
	);

	/**
	 * Creates a new instance of the Paginator class.
	 *
	 * Options can be configured by passing them through the $config array.
	 *
	 * @see li3_paginate\extensions\helper\Paginator::_init()
	 * @param array $config An array of options that can be configured during construction.
	 *     They allow for the easy alteration of text used on prev/next links,
	 *     and adjustment of the separator string. Valid options are:
	 *        - `'showFirstLast'`: Include/Exclude "<< First" and "Last >>" links when calling the paginate() method.
	 *        - `'showPrevNext'`: Include/Exclude "< Prev" and "Next >" links when calling the paginate() method.
	 *        - `'showNumbers'`: Include/Exclude "1 | 2 | 3" numeric links when calling the paginate() method.
	 *        - `'firstText'`: Overrides markup used for "<< First" anchor tag.
	 *        - `'prevText'`: Overrides markup used for "< Prev" anchor tag.
	 *        - `'nextText'`: Overrides markup used for "Next >" anchor tag.
	 *        - `'lastText'`: Overrides markup used for "Last >>" anchor tag.
	 *        - `'firstTextDisabled'`: Overrides markup used for "<< First" anchor tag when on first page.
	 *        - `'prevTextDisabled'`: Overrides markup used for "< Prev" anchor tag when on first page.
	 *        - `'nextTextDisabled'`: Overrides markup used for "Next >" anchor tag when on last page.
	 *        - `'lastTextDisabled'`: Overrides markup used for "Last >>" anchor tag when on last page.
	 *        - `'activePageStyle'`: Sets the style to be used on the active numeric page link.
 	 *        - `'activePageClass'`: Sets the class to be used on the active numeric page link.
	 * @return object An instance of the Paginator class being constructed.
	 */
	public function __construct(array $config = array()) {
		$defaults = array(
			'showFirstLast' => true,
			'showPrevNext' => true,
			'showNumbers' => true,
			'firstText' => "<< First",
            'firstTextDisabled' => "",
			'prevText' => "< Prev",
			'prevTextDisabled' => "",
			'nextText' => "Next >",
			'nextTextDisabled' => "",
			'lastText' => "Last >>",
            'lastTextDisabled' => "",
			'activeOpenTag' => '<li class="active">',
			'openTag' => "<li>",
			'closeTag' => "</li>",
			'library' => null,
			'controller' => "",
			'action' => "",
            'maxNumbers' => 10

		);
		parent::__construct($config + $defaults);
	}

	/**
	 * Initializes the new instance of the Paginator class.
	 *
	 * Called immediately after construction, used to setup the new class with default values gathered from the current
	 * _context.
	 *
	 * @see li3_paginate\extensions\helper\Paginator::__construct()
	 * @see \lithium\template\View::_renderer
	 */
	protected function _init() {
		parent::_init();
		// setting up the _config array for use in links etc. with our string templates
		$this->_library = isset($this->_context->_config['request']->params['library']) ? $this->_context->_config['request']->params['library']:null;
		$this->_controller = $this->_context->_config['request']->params['controller'];
		$this->_action = $this->_context->_config['request']->params['action'];
		$this->_page = ($this->_context->_config['data']['page'] + 0) ?: 1;
		$this->_total = $this->_context->_config['data']['total'];
		$this->_limit = $this->_context->_config['data']['limit'];
	}

	/**
	 * Creates the first page link
	 *
	 * @see li3_paginate\extensions\helper\Paginator::paginate()
	 * @return string Markup of the "<< First" page link.
	 */
	public function first(array $options = array()) {
		if (!empty($options)) {
			$this->config($options);
		}

		if ($this->_page > 1) {
			$config = array('page' => 1) + $this->_query();

			$url = \lithium\net\http\Router::match(
				$config + $this->_context->_config['request']->params,
				$this->_context->_config['request'],
				array('absolute' => true)
			);
			
			if(!empty($this->_library)) {
				$url['library'] = $this->_library;
			}

			return $this->_config['openTag'].$this->_context->html->link($this->_config['firstText'], $url).$this->_config['closeTag'];
		}
		return $this->_config['firstTextDisabled'];
	}

	/**
	 * Creates the previous page link
	 *
	 * @see li3_paginate\extensions\helper\Paginator::paginate()
	 * @return string Markup of the "< Prev" page link.
	 */
	public function prev(array $options = array()) {
		if (!empty($options)) {
			$this->config($options);
		}
		if ($this->_page > 1) {
			$config = array('page' => ($this->_page - 1)) + $this->_query();

			$url = \lithium\net\http\Router::match(
				$config + $this->_context->_config['request']->params,
				$this->_context->_config['request'],
				array('absolute' => true)
			);
			
			if(!empty($this->_library)) {
				$url['library'] = $this->_library;
			}

			return $this->_config['openTag'].$this->_context->html->link($this->_config['prevText'], $url).$this->_config['closeTag'];
		}
		return $this->_config['prevTextDisabled'];
	}

	/**
	 * Creates the next page link
	 *
	 * @see li3_paginate\extensions\helper\Paginator::paginate()
	 * @return string Markup of the "Next >" page link.
	 */
	public function next(array $options = array()) {
		if (!empty($options)) {
			$this->config($options);
		}
		if ($this->_total > ($this->_limit * $this->_page)) {
			$config = array('page' => ($this->_page + 1)) + $this->_query();

			$url = \lithium\net\http\Router::match(
				$config + $this->_context->_config['request']->params,
				$this->_context->_config['request'],
				array('absolute' => true)
			);
			
			if(!empty($this->_library)) {
				$url['library'] = $this->_library;
			}

			return $this->_config['openTag'].$this->_context->html->link($this->_config['nextText'], $url).$this->_config['closeTag'];
		}
		return $this->_config['nextTextDisabled'];
	}

	/**
	 * Creates the last page link
	 *
	 * @see li3_paginate\extensions\helper\Paginator::paginate()
	 * @return string Markup of the "Last >>" page link.
	 */
	public function last(array $options = array()) {
		if (!empty($options)) {
			$this->config($options);
		}
		$end = ceil(($this->_total / $this->_limit));
		if ($end > $this->_page) {
			$config = array('page' => $end) + $this->_query();

			$url = \lithium\net\http\Router::match(
				$config + $this->_context->_config['request']->params,
				$this->_context->_config['request'],
				array('absolute' => true)
			);
			
			if(!empty($this->_library)) {
				$url['library'] = $this->_library;
			}

			return $this->_config['openTag'].$this->_context->html->link($this->_config['lastText'], $url).$this->_config['closeTag'];
		}
		return $this->_config['lastTextDisabled'];
	}

	/**
	 * Creates the individual numeric page links, with the current link in the middle.
	 *
	 * @see li3_paginate\extensions\helper\Paginator::paginate()
	 * @return string Markup of the numeric page links.
	 */
	public function numbers(array $options = array()) {
		if (!empty($options)) {
			$this->config($options);
		}
        $maxNumbers = $this->_config['maxNumbers'];
        $addOne = false;
        if( ( $maxNumbers % 2 ) > 0) {
            $addOne = true;
            $maxNumbers--;
        }

        $minNumbersBefore = $maxNumbers/2;
        $maxNumbersAfter = $minNumbersBefore;

        if($addOne) {
            $maxNumbers++;
        }

		$start = ($this->_page - $minNumbersBefore);
		$end = ceil($this->_total / $this->_limit);
		if ($this->_page <= $minNumbersBefore) {
			$start = 1;
		}
		if (($this->_page + $maxNumbersAfter) < $end) {
			$end = ($this->_page + $maxNumbersAfter);
		}
		$buffer = "";
		
		$url = array(
			'controller' => $this->_controller,
			'action' => $this->_action
		);
		true;
		if(!empty($this->_library)) {
			$url['library'] = $this->_library;
		}
		
		for ($i = $start; $i <= $end; $i++) {
			$config = array('page' => $i) + $this->_query();
			$url = \lithium\net\http\Router::match(
				$config + $this->_context->_config['request']->params,
				$this->_context->_config['request'],
				array('absolute' => true)
			);
			if ($this->_page == $i) {
				$buffer .= $this->_config['activeOpenTag'].$this->_context->html->link($i, $url).$this->_config['closeTag'];
			} else {
				$buffer .= $this->_config['openTag'].$this->_context->html->link($i, $url).$this->_config['closeTag'];
			}
		}
		return $buffer;
	}

	/**
	 * Creates a full pagination control, based on configuration options defined during construction.
	 *
	 * @see li3_paginate\extensions\helper\Paginator::prev()
	 * @see li3_paginate\extensions\helper\Paginator::next()
	 * @see li3_paginate\extensions\helper\Paginator::numbers()
	 * @see li3_paginate\extensions\helper\Paginator::__construct()
	 * @see li3_paginate\extensions\helper\Paginator::$_strings
	 * @return string Markup of a full pagination control, based on config
	 *     eg: "< Prev | 1 | <strong>2</strong> | 3 | Next >".
	 */
	public function paginate(array $options = array()) {
		if (!empty($options)) {
			$this->config($options);
		}
		
		$this->_library = (empty($this->_config['library']) && isset($this->_context->_config['request']->params['library'])) ? $this->_context->_config['request']->params['library'] : $this->_config['library'];
		$this->_controller = (empty($this->_config['controller'])) ? $this->_context->_config['request']->params['controller'] : $this->_config['controller'];
		$this->_action = (empty($this->_config['action'])) ? $this->_context->_config['request']->params['action'] : $this->_config['action'];
		$content = "";
		if ($this->_config["showFirstLast"]) {
            $content .= $this->first();
        }
		if ($this->_config["showPrevNext"]) {
			$content .= $this->prev();
		}
		if ($this->_config["showNumbers"]) {
			$content .= $this->numbers();
		}
		if ($this->_config["showPrevNext"]) {
			$content .= $this->next();
		}
		if ($this->_config["showFirstLast"]) {
            $content .= $this->last();
        }
		return $this->_render(__METHOD__, 'pagingWrapper', compact('content'), array('escape' => false));
	}

	public function config(array $options = array()) {
		$this->_config = array_replace($this->_config, $options);
	}

	protected function _query() {
		$params = $this->_context->_config['request']->query;
		if (isset($params['url'])) {
			unset($params['url']);
		}
		if (count($params) > 0) {
			return array('?' => $params);
		}
		return array();
	}

}

?>