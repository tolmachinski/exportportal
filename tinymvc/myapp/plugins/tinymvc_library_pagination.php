<?php

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [03.12.2021]
 * library refactoring: code style, optimize code
 */
class TinyMVC_Library_Pagination
{
	var $site_url 			= __SITE_URL;
	var $base_url			= ''; // The page we are linking to
	var $prefix				= 'page/'; // A custom prefix added to the path.
	var $suffix				= ''; // A custom suffix added to the path.
	var $replace_url 		= false;

	var $total_rows			=  0; // Total number of items (database results)
	var $per_page			= 10; // Max number of items you want shown per page
	var $num_links			=  2; // Number of "digit" links to show before/after the currently viewed page
	var $cur_page			=  0; // The current page being viewed
	var $use_page_numbers	= TRUE; // Use page number for segment instead of offset
	var $first_link			= '<i class="ep-icon ep-icon_arrows-left"></i>';
	var $next_link			= '<i class="ep-icon ep-icon_arrow-right"></i>';
	var $prev_link			= '<i class="ep-icon ep-icon_arrow-left"></i>';
	var $last_link			= '<i class="ep-icon ep-icon_arrows-right"></i>';
//	var $uri_segment		= 3;
	var $full_tag_open		= '<ul class="pagination">';
	var $full_tag_close		= '</ul>';
	var $first_tag_open		= '<li>';
	var $first_tag_close	= '</li>';
	var $last_tag_open		= '<li>';
	var $last_tag_close		= '</li>';
	var $first_url			= ''; // Alternative URL for the First Page.
	var $cur_tag_open		= '<li class="active"><span>';
	var $cur_tag_close		= '</span></li>';
	var $next_tag_open		= '<li class="pagination__next">';
	var $next_tag_close		= '</li>';
	var $prev_tag_open		= '<li class="pagination__prev">';
	var $prev_tag_close		= '</li>';
	var $num_tag_open		= '<li>';
	var $num_tag_close		= '</li>';
	var $page_query_string	= FALSE;
	var $query_string_segment = 'per_page';
	var $display_pages		= TRUE;
	var $anchor_class		= '';
	var $start_uri_segment	= 0;

    /**
     * @param ContainerInterface $container The container instance
     */
	public function __construct(ContainerInterface $container)
	{
        $params = [];
        if ($container->hasParameter('library.pagination.params')) {
            $params = $container->getParameter('library.pagination.params') ?? [];
        }
		if (count($params) > 0) {
			$this->initialize($params);
		}

		if ($this->anchor_class != '') {
			$this->anchor_class = 'class="'.$this->anchor_class.'" ';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize Preferences
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 * @return	void
	 */
	function initialize($params = array())
	{
		if (!empty($params))
		{
			foreach ($params as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Generate the pagination links
	 *
	 * @access	public
	 * @return	string
	 */
	function create_links()
	{
		// If our item count or per-page total is zero there is no need to continue.
		if ($this->total_rows == 0 OR $this->per_page == 0)
		{
			return '';
		}

		// Calculate the total number of pages
		$num_pages = ceil($this->total_rows / $this->per_page);
		// Is there only one page? Hm... nothing more to do here then.
		if ($num_pages == 1)
		{
			return '';
		}

		// Set the base page index for starting page number
		if ($this->use_page_numbers)
		{
			$base_page = 1;
		}
		else
		{
			$base_page = 0;
		}

		// Determine the current page number.
        $link = app()->uri->uri_to_assoc($this->start_uri_segment);

		if (isset($link['page']) && $link['page'] != $base_page)
        {
            // Prep the current page - no funny business!
            $this->cur_page = (int) $link['page'];
        }

		// Set current page to 1 if using page numbers instead of offset
		if ($this->use_page_numbers AND $this->cur_page == 0)
		{
			$this->cur_page = $base_page;
		}

		$this->num_links = (int)$this->num_links;

		if ($this->num_links < 1)
		{
			throw new RuntimeException('Your number of links must be a positive number.');
		}

		if ( ! is_numeric($this->cur_page))
		{
			$this->cur_page = $base_page;
		}

		// Is the page number beyond the result range?
		// If so we show the last page
		if ($this->use_page_numbers)
		{
			if ($this->cur_page > $num_pages)
			{
				$this->cur_page = $num_pages;
			}
		}
		else
		{
			if ($this->cur_page > $this->total_rows)
			{
				$this->cur_page = ($num_pages - 1) * $this->per_page;
			}
		}

		$uri_page_number = $this->cur_page;

		if ( ! $this->use_page_numbers)
		{
			$this->cur_page = floor(($this->cur_page/$this->per_page) + 1);
		}

		// Calculate the start and end numbers. These determine
		// which number to start and end the digit links with
		$start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;
		$end   = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;

		// Is pagination being used over GET or POST?  If get, add a per_page query
		// string. If post, add a trailing slash to the base URL if needed
//		if ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)
//		{
//			$this->base_url = rtrim($this->base_url).'&amp;'.$this->query_string_segment.'=';
//		}
//		else
//		{
//			$this->base_url = rtrim($this->base_url, '/') .'/';
//		}

		if(!$this->replace_url){
			$this->base_url = rtrim($this->base_url, '/') .'/';
		}

		// And here we go...
		$output = '';

		// Render the "First" link
		if  ($this->first_link !== FALSE AND $this->cur_page > ($this->num_links + 1))
		{
			//$first_url = ($this->first_url == '') ? $this->base_url : $this->first_url;
			if($this->replace_url == true){
				$first_url = normalize_url($this->site_url . '/' . str_replace('page/' . config('replace_uri_template'), '', $this->base_url), false);
			} else{
				$first_url = $this->base_url;
			}

            $output .= $this->first_tag_open.'<a '.$this->anchor_class.'href="' . rtrim($first_url, '/') . '">'.$this->first_link.'</a>'.$this->first_tag_close;
		}

		// Render the "previous" link
		if  ($this->prev_link !== FALSE AND $this->cur_page != 1)
		{
			if ($this->use_page_numbers)
			{
				$i = $uri_page_number - 1;
			}
			else
			{
				$i = $uri_page_number - $this->per_page;
			}

			if ($i <= 1 && $this->first_url != '')
			{
				$output .= $this->prev_tag_open.'<a '.$this->anchor_class.'href="'.$this->first_url.'">'.$this->prev_link.'</a>'.$this->prev_tag_close;
			}
			else
			{
				if($this->replace_url == true){
					$current_url = replace_dynamic_uri($i, $this->base_url, $this->site_url);
				} else{
					$i = ($i == 0) ? '' : $this->prefix.$i.$this->suffix;
					$current_url = $this->base_url.$i;
				}
				$output .= $this->prev_tag_open.'<a '.$this->anchor_class.'href="'.$current_url.'">'.$this->prev_link.'</a>'.$this->prev_tag_close;
			}

		}

		// Render the pages
		if ($this->display_pages !== FALSE)
		{
			// Write the digit links
			for ($loop = $start -1; $loop <= $end; $loop++)
			{
				if ($this->use_page_numbers)
				{
					$i = $loop;
				}
				else
				{
					$i = ($loop * $this->per_page) - $this->per_page;
				}

				if ($i >= $base_page)
				{
					if ($this->cur_page == $loop)
					{
						$output .= $this->cur_tag_open.$loop.$this->cur_tag_close; // Current page
					}
					else
					{
						$n = ($i == $base_page) ? '' : $i;

						if ($n == '' && $this->first_url != '')
						{
							$output .= $this->num_tag_open.'<a '.$this->anchor_class.'href="'.$this->first_url.'">'.$loop.'</a>'.$this->num_tag_close;
						}
						else
						{
							if($this->replace_url == true){
								$current_url = replace_dynamic_uri($n, $this->base_url, $this->site_url);
							} else{
								$n = ($n == '') ? $this->suffix : $this->prefix.$n.$this->suffix;
								$current_url = $this->base_url.$n;
							}

							$output .= $this->num_tag_open.'<a '.$this->anchor_class.'href="'.$current_url.'">'.$loop.'</a>'.$this->num_tag_close;
						}
					}
				}
			}
		}

		// Render the "next" link
		if ($this->next_link !== FALSE AND $this->cur_page < $num_pages)
		{
			if ($this->use_page_numbers)
			{
				$i = $this->cur_page + 1;
			}
			else
			{
				$i = ($this->cur_page * $this->per_page);
			}

			if($this->replace_url == true){
				$current_url = replace_dynamic_uri($i, $this->base_url, $this->site_url);
			} else{
				$current_url = $this->base_url.$this->prefix.$i.$this->suffix;
			}

			$output .= $this->next_tag_open.'<a '.$this->anchor_class.'href="'.$current_url.'">'.$this->next_link.'</a>'.$this->next_tag_close;
		}

		// Render the "Last" link
		if ($this->last_link !== FALSE AND ($this->cur_page + $this->num_links) < $num_pages)
		{
			if ($this->use_page_numbers)
			{
				$i = $num_pages;
			}
			else
			{
				$i = (($num_pages * $this->per_page) - $this->per_page);
			}

			if($this->replace_url == true){
				$current_url = replace_dynamic_uri($i, $this->base_url, $this->site_url);
			} else{
				$current_url = $this->base_url.$this->prefix.$i.$this->suffix;
			}
			$output .= $this->last_tag_open.'<a '.$this->anchor_class.'href="'.$current_url.'">'.$this->last_link.'</a>'.$this->last_tag_close;
		}

		// Kill double slashes.  Note: Sometimes we can end up with a double slash
		// in the penultimate link so we'll kill all double slashes.
		$output = preg_replace("#([^:])//+#", "\\1/", $output);

		// Add the wrapper HTML if exists
		$output = $this->full_tag_open.$output.$this->full_tag_close;

		return $output;
	}
}
