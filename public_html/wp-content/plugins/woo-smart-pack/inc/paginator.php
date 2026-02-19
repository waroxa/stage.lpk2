<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'WooZnd_Paginator' ) ) {

    class WooZnd_Paginator {

        private $_total;
        private $_pagesize;
        private $_pageindex;
        private $_currentpage;
        private $_startrow;

        public function __construct( $total, $pagesize, $pageindex ) {
            
            $this->_total = $total;
            $this->_pagesize = $pagesize;
            $this->_pageindex = $pageindex - 1;
            $this->_currentpage = $pageindex;
            $this->_startrow = $pagesize * ($pageindex - 1);
        }

        public function isfirst() {
            if ( $this->_pageindex <= 0 ) {
                return false;
            } else {
                return true;
            }
        }

        public function isprev() {

            if ( $this->_pageindex <= 0 ) {
                return false;
            } else {
                return true;
            }
        }

        public function isnext() {
            if ( ($this->_total - $this->_startrow) <= $this->_pagesize ) {
                return false;
            } else {
                return true;
            }
        }

        public function islast() {
            if ( ($this->_total - $this->_startrow) <= $this->_pagesize ) {
                return false;
            } else {
                return true;
            }
        }

        public function fromrow() {

            if ( $this->_total <= 0 ) {
                return 0;
            } else {
                return $this->_startrow + 1;
            }
        }

        public function torow() {
            $start = $this->_startrow + $this->_pagesize;
            if ( $this->_total < $start ) {
                return $this->_total;
            } else {
                return $start;
            }
        }

        public function ofrow() {
            return $this->_total;
        }

        public function total() {
            return $this->_total;
        }

        public function skip() {
            return $this->_startrow;
        }

        public function offset() {
            return $this->skip();
        }

        public function take() {
            return $this->_pagesize;
        }

        public function limit() {
            return $this->take();
        }

        public function pages( $pset = 5 ) {
            $pgs = array();
            $startpage = 1;
            for ( $i = 0; $i < (($this->_total / $this->_pagesize) / $pset); $i++ ) {
                if ( $this->_currentpage > ($startpage + $pset - 1) ) {
                    $startpage += $pset;
                }
            }

            for ( $i = 0; $i < ($this->_total / $this->_pagesize) + 1; $i++ ) {
                if ( ($this->_total / $this->_pagesize) <= 1 ) {
                    break;
                }

                if ( $i >= $startpage ) {
                    $pgs[] = $i;
                }
                if ( ($this->_total - $startpage) <= $pset ) {
                    $pgs[] = $i;
                }

                if ( count( $pgs ) >= $pset ) {
                    break;
                }
            }
            return $pgs;
        }

        public function render_links( $url_format, $page_set = 5, $url_options = array(), $default_url = '', $space_char = '+' ) {

            $allowed_html = $this->get_allowed_html();

            if ( !($this->_total / $this->_pagesize > 1) ) {

                return;
            }

            if ( $this->isfirst() ) {

                echo wp_kses( $this->renderhtml( $url_format, 'first', 1, $url_options, $default_url ), $allowed_html );
            }

            if ( $this->isprev() ) {

                echo wp_kses( $this->renderhtml( $url_format, 'prev', $this->_currentpage - 1, $url_options, $default_url, $space_char ), $allowed_html );
            }

            $ppages = $this->pages( $page_set );


            if ( $ppages[ count( $ppages ) - 1 ] > count( $ppages ) ) {

                echo wp_kses( $this->renderhtml( $url_format, 'page-set-prev', $ppages[ 0 ] - count( $ppages ), $url_options, $default_url, $space_char ), $allowed_html );
            }

            foreach ( $ppages as $ppage ) {

                if ( $this->_currentpage == $ppage ) {

                    echo wp_kses( $this->renderhtml( $url_format, 'page-selected', $ppage, $url_options, $default_url, $space_char ), $allowed_html );
                } else {

                    echo wp_kses( $this->renderhtml( $url_format, 'page', $ppage, $url_options, $default_url, $space_char ), $allowed_html );
                }
            }

            if ( $ppages[ count( $ppages ) - 1 ] < $this->_total / $this->_pagesize ) {

                echo wp_kses( $this->renderhtml( $url_format, 'page-set-next', $ppages[ count( $ppages ) - 1 ] + 1, $url_options, $default_url, $space_char ), $allowed_html );
            }


            if ( $this->isnext() ) {

                echo wp_kses( $this->renderhtml( $url_format, 'next', $this->_currentpage + 1, $url_options, $default_url, $space_char ), $allowed_html );
            }

            if ( $this->islast() ) {

                echo wp_kses( $this->renderhtml( $url_format, 'last', $this->_total / $this->_pagesize, $url_options, $default_url, $space_char ), $allowed_html );
            }
        }

        public function render_woo_links( $url_format, $url_options = array(), $default_url = '', $space_char = '+', $prev = 'Previous', $next = 'Next' ) {
           
            $allowed_html = $this->get_allowed_html();
            
            if ( $this->isprev() ) {
            
                echo '<a href="' . esc_url( $this->get_url( $url_format, $this->_currentpage - 1, $url_options, $default_url, $space_char ) ) . '" class="woocommerce-Button woocommerce-Button--previous button">' . wp_kses( $prev, $allowed_html ) . '</a>';
            }
            
            if ( $this->isnext() ) {
                
                echo '<a href="' . esc_url( $this->get_url( $url_format, $this->_currentpage + 1, $url_options, $default_url, $space_char ) ) . '" class="woocommerce-Button woocommerce-Button--next button">' . wp_kses( $next, $allowed_html ) . '</a>';
            }
        }

        public function render_result_count( $format = '{{from}} to {{to}} of {{total}}' ) {

            $allowed_html = $this->get_allowed_html();

            $msg = preg_replace( '/{{from}}/', $this->fromrow(), $format );
            $msg2 = preg_replace( '/{{to}}/', $this->torow(), $msg );
            $msg3 = preg_replace( '/{{total}}/', $this->total(), $msg2 );

            echo wp_kses( $msg3, $allowed_html );
        }

        private function renderhtml( $url_format, $htmltype, $page = 1, $url_options = array(), $default_url = '', $space_char = '+' ) {
            $html_tag = '';
            switch ( $htmltype ) {
                
                case 'first':
                    $html_tag = $html_tag . '<a class="pg-link pg-first"';
                    $html_tag = $html_tag . ' href="' . $this->get_url( $url_format, $page, $url_options, $default_url, $space_char ) . '">';
                    $html_tag = $html_tag . '<span aria-hidden="true">«</span>' . '</a>';
                    break;
                
                case 'prev':
                    $html_tag = $html_tag . '<a class="pg-link pg-prev"';
                    $html_tag = $html_tag . ' href="' . $this->get_url( $url_format, $page, $url_options, $default_url, $space_char ) . '">';
                    $html_tag = $html_tag . '<span aria-hidden="true">‹</span>' . '</a>';
                    break;
                
                case 'next':
                    $html_tag = $html_tag . '<a class="pg-link pg-next"';
                    $html_tag = $html_tag . ' href="' . $this->get_url( $url_format, $page, $url_options, $default_url, $space_char ) . '">';
                    $html_tag = $html_tag . '<span aria-hidden="true">›</span>' . '</a>';
                    break;
                
                case 'last':
                    $html_tag = $html_tag . '<a class="pg-link pg-last"';
                    $html_tag = $html_tag . ' href="' . $this->get_url( $url_format, $page, $url_options, $default_url, $space_char ) . '">';
                    $html_tag = $html_tag . '<span aria-hidden="true">»</span>' . '</a>';
                    break;
                
                case 'page':
                    $html_tag = $html_tag . '<a class="pg-link pg-page"';
                    $html_tag = $html_tag . ' href="' . $this->get_url( $url_format, $page, $url_options, $default_url, $space_char ) . '">';
                    $html_tag = $html_tag . '<span aria-hidden="true">' . $page . '</span>' . '</a>';
                    break;
                
                case 'page-selected':
                    $html_tag = $html_tag . '<span class="pg-selected pg-page">';
                    $html_tag = $html_tag . '<span aria-hidden="true">' . $page . '</span>' . '</span>';
                    break;
                
                case 'page-set-prev':
                    $html_tag = $html_tag . '<a class="pg-link pg-page-set-prev"';
                    $html_tag = $html_tag . ' href="' . $this->get_url( $url_format, $page, $url_options, $default_url, $space_char ) . '">';
                    $html_tag = $html_tag . '<span aria-hidden="true">' . $page . '</span>' . '</a>';

                    $html_tag = $html_tag . '<span class="pg-text">';
                    $html_tag = $html_tag . '....</span>';
                    break;
                
                case 'page-set-next':
                    $html_tag = $html_tag . '<span class="pg-text">';
                    $html_tag = $html_tag . '....</span>';

                    $html_tag = $html_tag . '<a class="pg-link pg-page-set-next"';
                    $html_tag = $html_tag . ' href="' . $this->get_url( $url_format, $page, $url_options, $default_url, $space_char ) . '">';
                    $html_tag = $html_tag . '<span aria-hidden="true">' . $page . '</span>' . '</a>';
                    break;
                
                default :

                    break;
            }
            return $html_tag;
        }

        private function get_url( $url_format, $page = 1, $url_options = array(), $default_url = '', $space_char = '' ) {
            $url = '';
            if ( $page == 1 ) {
                $url = $default_url;
            } else {
                $url = preg_replace( '/{{page}}/', $page, $url_format );
            }
            $url = preg_replace( '/&/', '&amp;', $url );

            foreach ( $url_options as $key => $value ) {
                $url = preg_replace( '/{{' . $key . '}}/', $value, $url );
            }
            return preg_replace( '/ /', $space_char, $url );
        }

        private function get_allowed_html() {

            $allowed_html = array(
                'span' => array(
                    'id' => true,
                    'title' => true,
                    'aria-hidden' => true,
                    'class' => true,
                ),
                'a' => array(
                    'id' => true,
                    'href' => true,
                    'title' => true,
                    'class' => true,
                    'aria-hidden' => true,
                    'target' => true,
                ),
            );

            return $allowed_html;
        }

    }

}