<?php 
/**
 * Licensed under The MIT License
 */

// check ifanother plugin uses simple_html as well
if (!class_exists ("simple_html_dom")) {
    include_once("simple_html_dom.php");
}

class SmashwordsFunctions {
    	
    function getBooksFromSmashwords($params){

        // Options
        $author = $params['author'];
        if(empty( $params['author'])) {
            return;
            echo ("Author is empty, please correct your settings.");
        }
        $link_text = $params['link_text'];
        $affiliate = $params['affiliate'];
    
        // Contact Smashwords
        $url = "http://www.smashwords.com/profile/view/$author";
        $response = wp_remote_get($url);
        if( is_wp_error( $response ) ) {
           echo ('Error contacting Smashwords: '.$result->get_error_message());
           return;
        }
        $data = wp_remote_retrieve_body( $response  );
        $html = str_get_html($data);
    
        // Parse HTML from Smashwords look for:
        // <div class="library-book">
        //   <div class="col-sm-2">
        //     <a href="https://www.smashwords.com/books/view/326886">
        //       <img class="book-list-image" src="https://dwtr67e3ikfml.cloudfront.net/bookCovers/37e1de54f7f1ece5e1b84decfdf62fc2958a8cbb-thumb">
        //     </a>
        //   </div>
        //   <div class="text col-sm-10">
        //   <div style="clear: both;"></div>
        // </div>
        $allBooks = array();
        $i = 0;
        foreach($html->find('div[class=library-book]') as $div){
    	    $book = array();
    	    
            // image
            $a = explode("('", $imgUrl);
            $imgUrl = $div->find('img[class=book-list-image]');
            $imgUrl = $imgUrl[0]->src;
            $imgUrl = str_replace("tiny", "thumb", $imgUrl);
            $book['imgurl']=$imgUrl;

            // link
            $link = $div->find('a');
            $link = $link[0]->href;
            $book['link']=$link;
            
            $allBooks[$i] = $book;
            $i++;
        }
        $html->clear(); 
        unset($html);
        return $allBooks;
    }

    function getBooks($params){
        $author = $params['author'];
        // get data from cache
        if ( false === ( $allBooks = get_transient( $author ) ) ) {
            // It wasn't there, so regenerate the data and save the transient
            $allBooks = $this->getBooksFromSmashwords($params);
            set_transient( $author, $allBooks, 60*60*12 ); // 12h
        }
        return $allBooks;
    }
    
    function mergeBooks($allBooks, $books){
    	// TODO Dubletten rausfiltern:
    	// Mehrfache Arrayeinträge löschen
        // $foo = array_unique($foo);
        // print_r($foo);
    	
    	// Trick um ein frisches Array zurück zu bekommen
        // $foo = array_values(array_unique($foo));
        // print_r($foo);
    	return array_merge ( $allBooks, $books );
    }
    
    function getBooksFromAuthor($author){
    	// get data from cache
    // XXX	if ( false === ( $allBooks = get_transient( $author ) ) ) {
    		// It wasn't there, so regenerate the data and save the transient
    		$allBooks = $this->getBooksFromSmashwords($params);
    // XXX		set_transient( $author, $allBooks, 60*60*12 ); // 12h
    //XXX	}
    	return $allBooks;
    }

    function printOutput($params){
    	$author = $params['author'];
    	$url = "http://www.smashwords.com/profile/view/$author";
    	$slideEnabled = $params['slide']=='true';
        $slideWidth = 170;
        $slideHeight = 212;
        $verticalEnabled = $params['vertical']=='true';
    	$allBooks = $this->getBooks($params);
        $no_books = intval($params['no_books']);
        $no_books = min($no_books, count($allBooks));
        $visibleWidth = $verticalEnabled ? $slideWidth : ($no_books * $slideWidth);
        $visibleHeight = $verticalEnabled ? ($no_books * $slideHeight) : $slideHeight;
        if($slideEnabled) $no_books = count($allBooks); // as a slideshow we need to print all books
        $completeWidth = $verticalEnabled ? $slideWidth : ($no_books * $slideWidth);
        $completeHeight = $verticalEnabled ? ($no_books * $slideHeight) : $slideHeight;
        
        $current_book = array_rand($allBooks);
        echo('<!-- Smashwords Book Widget for Wordpress, for more information see http://unleashyouradventure.com/smashwords-book-widget-for-wordpress -->');
        echo('<div class="swbw_slideshow');
        if(!$slideEnabled) echo(' no_slides'); // marker for Javascript
        if($verticalEnabled) echo(' vertical'); // marker for Javascript
        echo('" style="width:'.($visibleWidth).'px;height:'.($visibleHeight).'px">');
        echo('<div class="swbw_slidesContainer" style="width:'.$visibleWidth.'px;height:'.($visibleHeight).'px">');
        echo('<div class="swbw_slideInner" style="width:'.$completeWidth.'px;height:'.($completeHeight).'px">');
        for($i=0; $i<$no_books; $i++){
        	$book = $allBooks[$current_book];
         	echo('<div class="swbw_slide" style="width:'.($slideWidth-4).'px">');
        	echo('<a href="'.$this->appendAffiliate($book['link'], $params).'" target="_blank">');
        	echo('<img alt="'.$book['title'].'" src="'.$book['imgurl'].'"></a>');
        	echo("</div>");
        	$current_book = $this->getNextBookNo($current_book, $allBooks);
        }
        echo('</div></div></div>');
        
        if( ! empty( $params['link_text'] )) {
            echo('<a href="');
            echo($this->appendAffiliate($url, $params));
            echo('" target="_blank">'.$params['link_text']."</a>");
        }
    }
    
    private function appendAffiliate($link, $params){
        if(! empty( $params['affiliate'] )){
            $link = $link."?ref=".$params['affiliate']; 
        }
        return $link;
    }
    
    private function getNextBookNo($currentNo, $allBooks){
    	$currentNo++;
    	if($currentNo>count($allBooks)-1){
    		$currentNo=0;
    	}
    	return $currentNo;
    }
}
?>