<?php
use Goutte\Client;

/**
 * DBIS-"Cloner", simliar to Typo3-Plugin
 * 
 * @note: maybe: http://www.sub.uni-hamburg.de/recherche/elektronische-angebote/datenbanken/neu-in-dbis.html
 * 
 * @todo Don't return formatted html but plain data
 * @todo REMOVE ALL " use ($php53)",  $php53 in general and check assignments
 */
class CloneDBIS {
    public $caller;    // script that created an instance of this class (~real url); SHOULD BE PRIVATE - public only for pre PHP 5.4
    private $caller_params = array();
    private $tpl_dir;
    public  $template = '';
    
    private $dbis_url = 'https://dbis.ur.de/';
    public  $dbis_id  = 'tuhh';

    // Config stuff (@see start_dbis())
    private $cfg_sort = 'alph'; //'alph' or 'type';
    private $cfg_lng;
  
    // Actual links
    public $link_home = '';
    public $link_advanced_search = '';
    public $link_alphabetically = '';
    public $link_collections = '';
    public $link_free_dbs = '';
    public $url_vanilla = '';
    public $link_vanilla = '';
    public $new_since_days = 365;
    public $link_new = '';
    public $link_dblink_sort = '';
    public $form_search = '';
        
    // Database access levels
    public $db_access_types = array();
    
    // Resultsets per page for further processing
    public $result_headline = '';
    public $result_fachliste = array();
    public $result_dbliste = array();
    public $result_dbliste_legend = array();
    public $result_detail = array();
    public $result_suche = array();
    
    // Only internal use
    public $current_category;       // SHOULD BE PRIVATE - public only for pre PHP 5.4

    
    /** /
     * @todo  Does not work if the page this class calls is accessed via url
     *        parameters (like there.net/wordpress/index.php?p=2)
     * @todo  unreliable: $this->tpl_dir
     */
    public function __construct($options = array()) {
        $this->caller = basename($_SERVER['SCRIPT_NAME']);
        $this->tpl_dir = '///'.$_SERVER['HTTP_HOST'].'/'.str_replace('\\', '/', dirname(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])))).'/templates/';

        // Process some options
        $this->set_config($options);
        $this->define_db_access_types();
        
        if (PHP_MAJOR_VERSION >= 7 || (PHP_MAJOR_VERSION >= 5 && PHP_MINOR_VERSION >= 4)) {
            require 'vendor/autoload.php';
        } elseif (PHP_MAJOR_VERSION >= 4 && PHP_MINOR_VERSION >= 3) {
            require 'vendor43/autoload.php';
        } else {
            die('DBIS Cloner requires PHP 4.3, better 5.4');
        }
    }
    
    public function start_dbis() {
        // Real start
        try {
            $this->dbis_proxy();
            $this->nav_links();        
            $this->output();
        } catch (Exception $ex) {
            echo ('<p>Bitte <a href="https://dbis.ur.de/index.php?bib_id="'.$this->dbis_id.'>DBIS direkt öffnen</a>. Die direkte Übersicht in dieser Seite ist aktuell leider gestört.</p>');
            //header('Location: https://dbis.ur.de/index.php?bib_id="'.$this->dbis_id);
        }
    }

    /**
     * Load options or set defaults
     */
    private function set_config($options) {
        // Language (could be more sophisticated to support more languages...)
        $lng = 'DE';
        if (isset($options['lng'])) {
            $lng = ($options['lng'] == 'EN') ? 'EN' : 'DE';
        } 
        include("locale/$lng.php");
        $this->cfg_lng = $strings;
        
        // Default sort mode (only two options)
        if (!isset($options['sort'])) {
            $this->cfg_sort = ($options['sort'] == 'alph') ? 'alph' : 'type';
        }
    }    
           
    /**
     * Using method instead of defining property directly => better overview
     * @note Got it from http://www.sub.uni-hamburg.de/en/recherche/elektronische-angebote/datenbanken.html ("Datenbanken anzeigen")
     */
    private function define_db_access_types() {
        $dbat[0] = $this->cfg_lng['dbat0'];
        //$dbat[1] = 'Online - auch extern';
        $dbat[1] = $this->cfg_lng['dbat1'];
        //$dbat[2] = 'Online - nur intern';
        $dbat[2] = $this->cfg_lng['dbat2'];
        $dbat[3] = $this->cfg_lng['dbat3'];
        $dbat[4] = $this->cfg_lng['dbat4'];
        $dbat[5] = $this->cfg_lng['dbat5'];
        $dbat[6] = $this->cfg_lng['dbat6'];
        $dbat[7] = $this->cfg_lng['dbat7'];
        $dbat[8] = $this->cfg_lng['dbat8'];
        $dbat[9] = $this->cfg_lng['dbat9'];
        //$dbat[300] = 'Pay-per-Use';
        $dbat[300] = $this->cfg_lng['dbat300'];
        //$dbat[500] = 'deutschlandweit frei (Nationallizenz)';
        $dbat[500] = $this->cfg_lng['dbat500'];
        
        $this->db_access_types = $dbat;
        
        return true;
    }
    
    
    /**
     * Only temporarily
     */
    private function nav_links() {
        $this->link_home = '<a href="'.$this->caller.'" class="dbis_nav">'.$this->cfg_lng['link_home'].'</a>';
        
        $this->link_advanced_search = '<a href="'.$this->caller.'?dbis=suche.php&bib_id='.$this->dbis_id.'" class="dbis_nav">'.$this->cfg_lng['link_advanced_search'].'</a>';

        $this->link_alphabetically = '<a href="'.$this->caller.'?dbis=dbliste.php&bib_id='.$this->dbis_id.'&lett=a&fc=1&lc=z" class="dbis_nav">'.$this->cfg_lng['link_alphabetically'].'</a>';
        
        $this->link_collections = '<a href="'.$this->caller.'?dbis=fachliste.php&bib_id='.$this->dbis_id.'&lett=s" class="dbis_nav">'.$this->cfg_lng['link_collections'].'</a>';
        
        $this->link_free_dbs = '<a href="'.$this->caller.'?dbis=fachliste.php&bib_id=allefreien" class="dbis_nav">'.$this->cfg_lng['link_free_dbs'].'</a>';

        $this->link_subject = '<a href="'.$this->caller.'?dbis=fachliste.php&bib_id='.$this->dbis_id.'&lett=l" class="dbis_nav">'.$this->cfg_lng['link_subject'].'</a>';
        
        $this->link_vanilla = '<a href="'.$this->url_vanilla.'" class="dbis_nav">'.$this->cfg_lng['link_vanilla'].'</a>';
        
        $recent = date('d.m.Y', strtotime("-$this->new_since_days days"));
        $this->link_new = '<a href="'.$this->caller.'?dbis=dbliste.php&bib_id='.$this->dbis_id.'&lett=k&jq_type1=LD&jq_term1='.$recent.'" class="dbis_nav">'.$this->cfg_lng['link_new'].'</a>'; //(letzten '.$this->new_since_days.' Tage)
        
        // Custom tub links
        $this->link_tub_collTD = '<a href="'.$this->caller.'?dbis=dbliste.php&bib_id='.$this->dbis_id.'&lett=c&collid=TD&sort='.$this->cfg_sort.'" class="dbis_nav">'.$this->cfg_lng['link_tub_collTD'].'</a>';

        $this->link_tub_collNL = '<a href="'.$this->caller.'?dbis=dbliste.php&bib_id='.$this->dbis_id.'&lett=c&collid=NL" class="dbis_nav">'.$this->cfg_lng['link_tub_collNL'].'</a>';

        $this->link_tub_collSH = '<a href="'.$this->caller.'?dbis=dbliste.php&bib_id='.$this->dbis_id.'&lett=c&collid=SH" class="dbis_nav">'.$this->cfg_lng['link_tub_collSH'].'</a>';

        $this->form_search = $this->get_simple_search();
    }

    
    /**
     * 
     */
    public function dbis_proxy() {
        if (isset($_GET['dbis'])) {
            // Fix 2017-10-10 umlauts must be iso-8859-1 urlencoded
            foreach ($_GET as $key => &$val) {
                $val = mb_convert_encoding($val, "iso-8859-1");
            }            
            
            $this->caller_params = $vanilla_params = $_GET;
            $target = $vanilla_params['dbis'];
            
            unset($vanilla_params['dbis']);
            $url = $this->dbis_url.$target.'?'.http_build_query($vanilla_params);
        } else {
            /* Fachliste as start page
            $target = 'fachliste.php';
            $url = $this->dbis_url.'fachliste.php?bib_id='.$this->dbis_id.'&lett=l';
            */
            // 26 DBliste Topdatenbanken als Startseite
            $target = 'dbliste.php';
            $url = $this->dbis_url.'dbliste.php?bib_id='.$this->dbis_id.'&lett=c&collid=TD&sort='.$this->cfg_sort;
            /* 2021-01-26: way to set "Alphabetisch" als Standard
            $target = 'dbliste.php';
            $url = $this->dbis_url.'dbliste.php?bib_id='.$this->dbis_id.'&lett=a&fc=1&lc=z';
            */
        }
        $this->url_vanilla = $url;
        
        //
        if ($target == 'fachliste.php') {
            $this->get_fachliste($url);
            $this->template = 'fachliste.tpl';
        } elseif ($target == 'dbliste.php') {
            $this->get_dblist($url);
            $this->template = 'dbliste.tpl';
        } elseif ($target == 'detail.php') {
            $this->get_detail($url);
            $this->template = 'detail.tpl';
        } elseif ($target == 'suche.php') {
            $this->get_advanced_search_form();
            $this->template = 'suche.tpl';
        } else {
            die('WTF, where did you click?');
        }
    }

    /** 
     * Finally move this out of this class, let it handle the wordpress plugin
     */
    public function output() {
        include('templates/default/default_header.tpl');
        include('templates/default/'.$this->template);
        include('templates/default/default_footer.tpl');
    }
    
    
    /**
     * Default entry page
     * 
     * @return ARY Populates CloneDBIS::result_fachliste
     * 
     * @note    It's quite a bit faster to just echo the output here. Decided to 
     *          use the more flexible way with the returned result array (for 
     *          now). 
     */
    private function get_fachliste($url) {
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $this->result_headline = trim($crawler->filterXPath('//p[@class="headline"]')->text());
        if (isset($this->cfg_lng[$this->result_headline])) $this->result_headline = $this->cfg_lng[$this->result_headline];
        
        // list with headings
        $list = $crawler->filterXPath('//div[@class="search_table"]/table//tr[td/@class[starts-with(.,"normal_body")]]');
        if ($list->count()) {
            $php53 = $this;
            $this->result_fachliste = $list->each(function ($node) use ($php53) {
                $name = trim($node->filterXPath('//td[1]')->text());
                $href = $node->filterXPath('//td[1]/a/@href')->text();
                parse_str($href);
                $id = (isset($gebiete)) ? $gebiete : false;
                $link = trim($node->filterXPath('//td[1]')->html());
                $hits = $node->filterXPath('//td[2]')->html();

                $href = str_replace('dbliste.php?', $php53->caller.'?dbis=dbliste.php&sort='.$this->cfg_sort.'&', $href);
                $link = str_replace('dbliste.php?', $php53->caller.'?dbis=dbliste.php&sort='.$this->cfg_sort.'&', $link);

                $entry = array(
                    'id'    => $id,
                    'name'  => $name,
                    'link'  => $link,
                    'href'  => $href,
                    'hits'  => $hits
                );
                
                //echo "$link ($hits)<br>\n";
                return $entry;
            });        
        }

        return true;
    }
      
    
    /**
     * Gets complete list of DBs - with paging through result set
     * 
     * @todo Don't take url as argument but, "lett=f&gebiete=28"
     * @param type $url
     */
    private function get_dblist($url) {        
        // Get current sort mode
        //http_build_query($this->caller_params);

        // START TMP 2016-10-20 (for sort link after first page call)
        if (!isset($this->caller_params['dbis'])) {
            $this->caller_params['dbis'] = 'dbliste.php';
            $this->caller_params['bib_id'] = 'tuhh';
            $this->caller_params['lett'] = 'c';
            $this->caller_params['collid'] = 'TD';
        }
        // END TMP 2016-10-20 (for sort link after first page call)
        if (!isset($this->caller_params['sort'])) {
            $this->caller_params['sort'] = $this->cfg_sort;
        } 
        
        if ($this->caller_params['sort'] == 'type') {
            $this->caller_params['sort'] = 'alph';
            $link_txt = $this->cfg_lng['btn_sort_alph'];
        } elseif ($this->caller_params['sort'] == 'alph') {
            $this->caller_params['sort'] = 'type';
            $link_txt = $this->cfg_lng['btn_sort_type'];
        }
        
        // Disable sort for 'a' (alphabetically; added 2021-01-26)
        if ($this->caller_params['lett'] == 'c') {
            $this->link_dblink_sort = '<a href="'.$this->caller.'?'.http_build_query($this->caller_params).'" id="link_sorting">'.$link_txt.'</a>';
        }
                
        $client = new Client();
        $crawler = $client->request('GET', $url);
        
        $this->result_headline = trim($crawler->filterXPath('//p[@class="headline"]')->text());
        if (isset($this->cfg_lng[$this->result_headline])) $this->result_headline = $this->cfg_lng[$this->result_headline];


        // Paging
        $dblist = array();
        do {
            $result = array_merge($dblist, $this->get_dblist_singlePage($crawler));
            // Next entry
            if ($more = $crawler->filterXPath('//a[.="nächste Treffer >"]')->count()) {
                $link = $crawler->filterXPath('//a[.="nächste Treffer >"]')->link();
                $crawler = $client->click($link);
            }
        } while ($more);

        return $dblist;
    }

    
    /** 
     * Scrape db names and links from html table
     * 
     * @param type $dom
     * @return type
     */
    private function get_dblist_singlePage($dom) {
        $list = $dom->filterXPath('//div[@class="search_table"]/table//tr[td/@class[starts-with(.,"normal_")]]');
        if ($list->count()) {
            $php53 = $this;
            $page_rows = $list->each(function ($node) use ($php53) {
                $category = $name = $link = $href = $id = $access_text = $access_lib = $access_id = '';

                // $node holds the three table columns. There are three different row types: 'normal_head', 'normal_body' and 'normal_footer'
                switch ($node->filterXPath('//td[1]/@class')->text()) {
                    case 'normal_head': 
                        $category = trim($node->filterXPath('//td[1]/text()')->text());
                        $category = str_replace(' Treffer', '', $category);
                        // Erläuterungen gäb es hier: http://rzblx10.uni-regensburg.de/dbinfo/index.php?bib_id=tuhh&ref=db_type#db_type_3
                        break;
                    case 'normal_body': 
                        $name = trim($node->filterXPath('//td[1][a/@href]')->text());
                        $link = trim($node->filterXPath('//td[1][a/@href]')->html());
                        $href = $node->filterXPath('//td[1]/a/@href')->text();
                        parse_str($href);
                        $id = $titel_id;
                        $access_text = $node->filterXPath('//td[2]')->text();
                        $access_lib = $node->filterXPath('//td[3]')->html();
                        break;
                    case 'normal_footer':
                        // Who cares...?
                    default:    
                        //
                }          

                if ($category) {
                    $php53->current_category = $category;
                    return $category;
                    //echo "<h1>$category</h1>";
                } 
                // @todo: Strange. Sometimes there is an icon but an empty db name. Anyway, this is correct
                elseif ($name) {
                    //Change link (make this script behave kinda proxy)
                    $link = str_replace('detail.php?', $php53->caller.'?dbis=detail.php&', $link);

                    //replace access logo (could just copy folder structure; it's like: icons/tuhh/zXXX.gif); CHECK for better generic way
                    if (strpos($access_lib, 'z0.gif') || !strpos($access_lib, '<img')) {
                        // no img if calling http://rzblx10.uni-regensburg.de/dbinfo/fachliste.php?bib_id=allefreien
                        $access_id = 0;
                    } 
                    elseif (strpos($access_lib, 'z1.gif'))   { $access_id = 1; }
                    elseif (strpos($access_lib, 'z2.gif'))   { $access_id = 2; }
                    elseif (strpos($access_lib, 'z3.gif'))   { $access_id = 3; }
                    elseif (strpos($access_lib, 'z4.gif'))   { $access_id = 4; }
                    elseif (strpos($access_lib, 'z5.gif'))   { $access_id = 5; }
                    elseif (strpos($access_lib, 'z6.gif'))   { $access_id = 6; }
                    elseif (strpos($access_lib, 'z7.gif'))   { $access_id = 7; }
                    elseif (strpos($access_lib, 'z8.gif'))   { $access_id = 8; }
                    elseif (strpos($access_lib, 'z9.gif'))   { $access_id = 9; }
                    elseif (strpos($access_lib, 'euro.gif')) { $access_id = 300; }
                    elseif (strpos($access_lib, 'z-de.gif')) { $access_id = 500; }
                    else {
                        // Should not exist
                    }
                
                    $php53->result_dbliste_legend[$access_id] = array (
                        'access_id' => $access_id,
                        'text'      => $php53->db_access_types[$access_id]
                    );
                            
                    $entry = array(
                        'cat'       => $php53->current_category,
                        'id'        => $id,
                        'name'      => $name,
                        'link'      => $link,
                        'href'      => $href,
                        'access_id' => $access_id
                    );
                    
                    $php53->result_dbliste[$php53->current_category][] = $entry;

                    //echo  "$db_access_lib $link<br/>";
                    return $entry;
                }
            });        
        } else {
            // Search without results is the only "else"...
            // Note: dbis shows lots and lots of information how to adjust search terms
            $entry = array(
                'cat'       => '',
                'id'        => '',
                'name'      => $this->cfg_lng['srch_no_result'],
                'link'      => $this->cfg_lng['srch_no_result'],
                'href'      => '',
                'access_id' => ''
            );
            
            $this->result_dbliste['Suche'][] = $entry;
            
            //echo  "Ihre Suche lieferte leider keine Ergebnisse.<br/>";
            return $entry;
        }
        
        return $page_rows;
    }

    
    /**
     * 
     * @todo Don't use $url but "titel_id=1302"
     * @todo Only fetch data. Use separate class or template for layouting
     * @param type $url
     */
    private function get_detail($url) {
        $client = new Client();
        $crawler = $client->request('GET', $url);
        
        $this->result_headline = trim($crawler->filterXPath('//p[@class="headline"]')->text());
        
        $list = $crawler->filterXPath('//div[@class="single_hit"]/table//tr[td/@class[starts-with(.,"normal_")]]');       
        if ($list->count()) {
            $php53 = $this;
            $page_rows = $list->each(function ($node) use ($php53) {
                $db_title = $description_heading = $description_content = '';

                // $node holds the three table columns. There are three different row types: 'normal_head', 'normal_body' and 'normal_footer'
                switch ($node->filterXPath('//td[1]/@class')->text()) {
                    case 'normal_head': 
                        $db_title = trim($node->filterXPath('//td[1]')->text());
                        break;
                    case 'normal_body': 
                        $description_id = $node->filterXPath('//td[1]/@id')->text();
                        $description_heading = $node->filterXPath('//td[1]')->html();
                        // "alle freien" > Details => there is a select field "Bibliothek(en) mit Bestandsnachweis:"...?
                        if ($node->filterXPath('//td[2]')->count()) {
                            $description_content = $node->filterXPath('//td[2]')->html();
                        }
                        $description_content = $php53->adjust_detail_rows($description_id, $description_content);
                        break;
                    case 'normal_footer':
                        // Who cares...?
                    default:    
                        //
                }          

                if ($db_title) {
                    $description_id = 'detail_title';
                    $description_heading = $db_title;
                    $description_content = '';
                    $row = '<table><tr id="detail_title"><th colspan="2">'.$db_title.'</td></tr>';
                } else {
                    if (isset($this->cfg_lng[$description_heading])) $description_heading = $this->cfg_lng[$description_heading];
                    $row = '<tr id="'.$description_id.'"><th>'.$description_heading.'</th><td>'.$description_content.'</td></tr>';
                }
                
                $entry[$description_id] = array(
                    'row_id'    => $description_id,
                    'heading'   => $description_heading,
                    'content'   => $description_content,
                    'html'      => $row
                );
                    
                $php53->result_detail['rows'] = $entry;

                return $row;
            });        
        } else {
            return false;
        }
        $page_rows[] = '</table>';
        
        $this->result_detail['default'] = implode($page_rows, "\n");

        return true;
    }

    
    /**
     * Way to enhance details. Separate method for overview. @see get_detail()
     * 
     * @todo check how to handle detail_db_types
     * @param type $id
     * @todo MAKE PRIVATE IF NOT $php53
     * @todo Check "2016-12-01 TEMP" comments (http to https hacks)
     */
    public function adjust_detail_rows($id, $content) {
        switch($id) {
            case 'detail_more_titles':
                // Sometimes pretty long list (break for each entry), but change nothing for now
                break;
            case 'detail_start':
                // Set correct link for database. DBIS uses a a special link for statistical purposes
                $content = str_replace('a href="', 'a href="'.$this->dbis_url, $content);
                break;
            case 'detail_more_licensed_starts':
                // 2016-12-01 TEMP: Replace Shibboleth image http link with https
                $content = str_replace('http://sfx.gbv.de/sfx_tuhh/img/sfxmenu/shibb.png', 'https://sfx.gbv.de/sfx_tuhh/img/sfxmenu/shibb.png', $content);
                break;
            case 'detail_access':
                // Replace image > first make the replacement/license getting more clever @see get_dblist_singlePage()
                    // 2016-12-01 TEMP: do it anyway temporarily
                    $content = str_replace('http:', 'https:', $content);
                // can be tricky: http://rzblx10.uni-regensburg.de/dbinfo/detail.php?bib_id=tuhh&colors=&ocolors=&lett=f&tid=0&titel_id=835
                break;
            case 'detail_hints':
                // Nothing to change here really...
            case 'detail_content':
                // Nothing to change here really...
                break;
            case 'detail_subjects':
                // Maybe it would be nice to link the subjects (search
                $subjects = explode('<br>', trim($content));
                $subjects = array_filter($subjects, 'strlen'); //remove empty entries
                $content = '<ul ><li>'.implode('</li><li>', $subjects).'</li></ul>';
                break;
            case 'detail_keywords':
                $linking = explode('<br>', trim($content));
                $linking = array_filter($linking, 'strlen'); //remove empty entries
                natsort($linking);
                // Maybe linking it without the library would make more sense?
                //$bib_id = 'alle';
                $bib_id = $this->dbis_id;
                foreach ($linking AS $index => &$keyword) {
                    $keyword = trim($keyword);
                    $keyword = '<a href="'.$this->caller.'?dbis=dbliste.php&bib_id='.$_GET['bib_id'].'&lett=k&jq_type1=KW&jq_term1='.$keyword.'">'.$keyword.'</a>';
                }
                $content = implode(', ', $linking);
                break;
            case 'detail_appearance':
                // Exciting facts here, nothing to change
                break;
            case 'detail_db_types':
                // here is the info link "mehr" - remove for now
                preg_match_all('/style="font-size:1em;">\s*(.*?)<script/', $content, $matches, PREG_PATTERN_ORDER);
                $content = implode('<br>', $matches[1]);
                break;
            case 'detail_report_periods':
                // Nothing to change here really...
                break;
            case 'detail_publisher':
                // Exciting facts here, nothing to change
                break;
            case 'detail_remarks':
                // Exciting facts here, nothing to change
                // 2016-12-01 TEMP: Replace Shibboleth image http link with https
                $content = str_replace('http://sfx.gbv.de:9004/sfx_tuhh/sfx.gif', 'https://sfx.gbv.de/sfx_tuhh/sfx.gif', $content);
                $content = str_replace('http://sfx.gbv.de/sfx_tuhh/sfx.gif', 'https://sfx.gbv.de/sfx_tuhh/sfx.gif', $content);
                break;
            default:
                //
        }
        
        return $content;
    }
    
    
    /**
     * Just fetch the form and replace ("proxifiy") the action url. Search 
     * results are processed in get_dblist().
     * @todo    Use some "cleaner" $node->add instead of str_replace
     */
    public function get_advanced_search_form() {
        $url = $this->dbis_url.'suche.php?bib_id='.$this->dbis_id;
        $client = new Client();
        $crawler = $client->request('GET', $url);
        
        $this->result_headline = trim($crawler->filterXPath('//p[@class="headline"]')->text());

        $form = $crawler->filterXPath('//td[form]')->html();
        $form = str_replace('<form method="get" action="dbliste.php">', '<form method="get" action="'.$this->caller.'"><input type="hidden" name="dbis" value="dbliste.php">', $form);
        
        // echo $form;
        $this->result_suche[]['html'] = $form;
        
        return true;
    }

    
    /**
     * It's so simple. So, don't scrape it, but write it.
     * 
     * @example http://rzblx10.uni-regensburg.de/dbinfo/dbliste.php?bib_id=tuhh&colors=7&ocolors=40&lett=fs&Suchwort=test
     */
    public function get_simple_search() {
        $form = '<form id="dbis_search" method="get" action="'.$this->caller.'">'.$this->link_advanced_search.'&nbsp;
                    <input type="hidden" name="dbis" value="dbliste.php">
                    <input type="hidden" name="bib_id" value="'.$this->dbis_id.'">
                    <input type="hidden" name="lett" value="fs">
                    <input type="text" name="Suchwort" placeholder="'.$this->cfg_lng['simple_search_input'].'" />
                 </form>';
        
        return $form;
    }
        
}
?>