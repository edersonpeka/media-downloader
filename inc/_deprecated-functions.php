<?php

/* -- CASE SPECIFIC: -- */

function listarCategorias($t){
    preg_match_all('/\[cat:([^\]]*)\]/i',$t,$matches);
    if(count($matches)){
        foreach($matches[1] as $catname){
            $myposts = get_posts(array('numberposts'=>-1,'post_type'=>'post','category_name'=>$catname,'suppress_filters'=>0));
            $listposts='';

            if(count($myposts)){
                global $post;
                $prepost=$post;
                $listposts.='<ul class="inner-cat">';
                foreach($myposts as $post) $listposts.='<li><a href="'.get_permalink().'">'.get_the_title().'</a></li>';
                $listposts.='</ul>';
                $post=$prepost;
            }
            $t = tiraDoParagrafo('[cat:'.$catname.']', $t);
            $t = str_replace('[cat:'.$catname.']', $listposts, $t);
        }
    }
    return $t;
}

function listarCategoriasEx($t){
    preg_match_all('/\[catex:([^\]]*)\]/i',$t,$matches);
    if(count($matches)){
        foreach($matches[1] as $catname){
            $myposts = get_posts(array('post_type'=>'post','category_name'=>$catname,'suppress_filters'=>0));
            $listposts='';
            if(count($myposts)){
                global $post;
                $prepost=$post;
                $listposts.='<dl class="inner-cat">';
                foreach($myposts as $post) $listposts.='<dt><a href="'.get_permalink().'">'.get_the_title().'</a></dt>'.(trim($post->post_excerpt)?'<dd>'.$post->post_excerpt.'</dd>':'');
                $listposts.='</dl>';
                $post=$prepost;
            }
            $t = tiraDoParagrafo('[catex:'.$catname.']', $t);
            $t = str_replace('[catex:'.$catname.']', $listposts, $t);
        }
    }
    return $t;
}

function listarIdiomas($t){
    if ( mb_stripos($t, '[languages]')!==false && function_exists('qtrans_generateLanguageSelectCode') ){
        ob_start();
        qtrans_generateLanguageSelectCode();
        $i=ob_get_contents();
        ob_end_clean();
        ob_end_flush();
        $t = tiraDoParagrafo('[languages]', $t);
        $t = str_replace('[languages]', $i, $t);
    }
    return $t;
}

function tiraDoParagrafo($tag, $t){
    return str_replace('<p>'.$tag.'</p>', $tag, $t);
}

/* -- END CASE SPECIFIC; -- */
