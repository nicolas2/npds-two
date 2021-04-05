<?php
/**
 * Npds Two
 *
 * Based on NPDS Copyright (c) 2002-2020 by Philippe Brunier
 * 
 * @author Nicolas2
 * @version 1.0
 * @date 02/04/2021
 */
namespace npds\pagination;


/*
 * pagination
 */
class pagination {


    /**
     * Retourne un bloc de pagination
     * @param  [type] $url             [description]
     * @param  [type] $urlmore         [description]
     * @param  [type] $total           [description]
     * @param  [type] $current         [description]
     * @param  [type] $adj             [description]
     * @param  [type] $topics_per_page [description]
     * @param  [type] $start           [description]
     * @return [type]                  [description]
     */
    public static function paginate_single($url, $urlmore, $total, $current, $adj, $topics_per_page, $start) 
    {
        // page précédente
        $prev = $current - 1; 
        // page suivante
        $next = $current + 1; 
        // avant-dernière page
        $penultimate = $total - 1; 
        $pagination = '';
           

        if ($total > 1) 
        {
            $pagination .= '
            <nav>
            <ul class="pagination pagination-sm d-flex flex-wrap">';
              
            if ($current == 2)
            {
                $pagination .= '<li class="page-item"><a class="page-link" href="'.$url.$urlmore.'" title="'.translate("Page précédente").'" data-toggle="tooltip">◄</a></li>';
            }
            elseif ($current > 2)
            {
                $pagination .= '<li class="page-item"><a class="page-link" href="'.$url.$prev.$urlmore.'" title="'.translate("Page précédente").'" data-toggle="tooltip">◄</a></li>';
            }
            else
            {
                $pagination .= '<li class="page-item disabled"><a class="page-link" href="#">◄</a></li>';
            }

            /*
             * Début affichage des pages, l'exemple reprend le cas de 3 numéros de 
             * pages adjacents (par défaut) de chaque côté du numéro courant
             * - CAS 1 : il y a au plus 12 pages, insuffisant pour faire une troncature
             * - CAS 2 : il y a au moins 13 pages, on effectue la troncature pour afficher 
             * 11 numéros de pages au total
             */

            //  CAS 1 : au plus 12 pages -> pas de troncature
            if ($total < 7 + ($adj * 2)) 
            {
                $pagination .= ($current == 1) ? '<li class="page-item active"><a class="page-link" href="#">1</a></li>' : '<li class="page-item"><a class="page-link" href="'.$url.$urlmore.'">1</a></li>';
                 
                for ($i=2; $i<=$total; $i++) 
                {
                    if ($i == $current)
                    {
                        $pagination .= '<li class="page-item active"><a class="page-link" href="#">'.$i.'</a></li>';
                    }
                    else
                    {
                        $pagination .= '<li class="page-item"><a class="page-link" href="'.$url.$i.$urlmore.'">'.$i.'</a></li>';
                    }
                }
            }
            //  CAS 2 : au moins 13 pages -> troncature
            else 
            {
                /* Troncature 1 : 1 2 3 4 5 6 7 8 9 … 16 17 */
                if ($current < 2 + ($adj * 2)) 
                {
                    $pagination .= ($current == 1) ? '<li class="page-item active"><a class="page-link" href="#">1</a></li>' : '<li class="page-item"><a class="page-link" href="'.$url.'">1</a></li>';
                    
                    for ($i = 2; $i < 4 + ($adj * 2); $i++) 
                    {
                        if ($i == $current) 
                        {
                            $pagination .= '
                            <li class="page-item active"><a class="page-link" href="#">'.$i.'</a></li>';
                        }
                        else 
                        {
                            $pagination .= '
                            <li class="page-item"><a class="page-link" href="'.$url.$i.$urlmore.'">'.$i.'</a></li>';
                        }
                    }

                    $pagination .= '
                    <li class="page-item disabled"><a class="page-link" href="#">&hellip;</a></li>
                    <li class="page-item"><a class="page-link" href="'.$url.$penultimate.$urlmore.'">'.$penultimate.'</a></li>
                    <li class="page-item"><a class="page-link" href="'.$url.$total.$urlmore.'">'.$total.'</a></li>';
                }
                /* Troncature 2 : 1 2 … 5 6 7 8 9 10 11 … 16 17 */
                elseif ( (($adj * 2) + 1 < $current) && ($current < $total - ($adj * 2)) ) 
                {
                    $pagination .= '
                    <li class="page-item"><a class="page-link" href="'.$url.'1'.$urlmore.'">1</a></li>
                    <li class="page-item"><a class="page-link" href="'.$url.'2'.$urlmore.'">2</a></li>
                    <li class="page-item disabled"><a class="page-link" href="#">&hellip;</a></li>';
                    
                    for ($i = ($current - $adj); $i <= $current + $adj; $i++) 
                    {
                        if ($i == $current) 
                        {
                            $pagination .= '
                            <li class="page-item active"><a class="page-link" href="#">'.$i.'</a></li>';
                        }
                        else 
                        {
                            $pagination .= '
                            <li class="page-item"><a class="page-link" href="'.$url.$i.$urlmore.'">'.$i.'</a></li>';
                        }
                    }

                    $pagination .= '
                    <li class="page-item disabled"><a class="page-link" href="#">&hellip;</a></li>
                    <li class="page-item"><a class="page-link" href="'.$url.$penultimate.$urlmore.'">'.$penultimate.'</a></li>
                    <li class="page-item"><a class="page-link" href="'.$url.$total.$urlmore.'">'.$total.'</a></li>';
                }
                /* Troncature 3 : 1 2 … 9 10 11 12 13 14 15 16 17 */
                else 
                {
                    $pagination .= '
                    <li class="page-item"><a class="page-link" href="'.$url.'1'.$urlmore.'">1</a></li>
                    <li class="page-item"><a class="page-link" href="'.$url.'2'.$urlmore.'">2</a></li>
                    <li class="page-item disabled"><a class="page-link" href="#">&hellip;</a></li>';

                    for ($i = $total - (2 + ($adj * 2)); $i <= $total; $i++) 
                    {
                        if ($i == $current) 
                        {
                            $pagination .= '
                            <li class="page-item active"><a class="page-link" href="#">'.$i.'</a></li>';
                        }
                        else
                        { 
                            $pagination .= '
                            <li class="page-item"><a class="page-link" href="'.$url.$i.$urlmore.'">'.$i.'</a></li>';
                        }
                    }
                }
            }

            if ($current == $total)
            {
                $pagination .= '
                <li class="page-item disabled"><a class="page-link" href="#">►</a></li>';
            }
            else
            {
                $pagination .= '
                <li class="page-item"><a class="page-link" href="'.$url.$next.$urlmore.'" title="'.translate("Page suivante").'" data-toggle="tooltip">►</a></li>';
            }
              
            $pagination .= '
                </ul>
            </nav>';
        }
           
        return $pagination;
    }

    /**
     * Retourne un bloc de pagination
     * @param  [type] $url             [description]
     * @param  [type] $urlmore         [description]
     * @param  [type] $total           [description]
     * @param  [type] $current         [description]
     * @param  [type] $adj             [description]
     * @param  [type] $topics_per_page [description]
     * @param  [type] $start           [description]
     * @return [type]                  [description]
     */
    public static function paginate($url, $urlmore, $total, $current, $adj, $topics_per_page, $start) 
    {
        // page précédente
        $prev = $start - $topics_per_page; 
        // page suivante
        $next = $start + $topics_per_page;
        
        //avant-dernière page
        $penultimate = $total - 1; 
        $pagination = '';
           
        if ($total > 1) 
        {
            $pagination .= '
            <nav>
                <ul class="pagination pagination-sm d-flex flex-wrap">';
                
                if ($current == 1) 
                {
                    $pagination .= '
                    <li class="page-item"><a class="page-link" href="'.$url.'0'.$urlmore.'" title="'.translate("Page précédente").'" data-toggle="tooltip">◄</a></li>';
                } 
                elseif ($current > 1) 
                {
                    $pagination .= '
                    <li class="page-item"><a class="page-link" href="'.$url.$prev.$urlmore.'" title="'.translate("Page précédente").'" data-toggle="tooltip">◄</a></li>';
                } 
                else 
                {
                    $pagination .= '
                    <li class="page-item disabled"><a class="page-link" href="#">◄</a></li>';
                }

                /**
                 * Début affichage des pages, l'exemple reprend le cas de 3 numéros de 
                 * pages adjacents (par défaut) de chaque côté du numéro courant
                 * - CAS 1 : il y a au plus 12 pages, insuffisant pour faire une troncature
                 * - CAS 2 : il y a au moins 13 pages, on effectue la troncature pour 
                 * afficher 11 numéros de pages au total
                 */

                //  CAS 1 : au plus 12 pages -> pas de troncature
                if ($total < 7 + ($adj * 2)) 
                {
                    $pagination .= ($current == 0) ? '
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>' : '<li class="page-item"><a class="page-link" href="'.$url.'0'.$urlmore.'">1</a></li>';
                    for ($i=2; $i<=$total; $i++) 
                    {
                        if ($i == $current+1)
                        {
                            $pagination .= '
                            <li class="page-item active"><a class="page-link" href="#">'.$i.'</a></li>';
                        }
                        else
                        {
                            $pagination .= '
                            <li class="page-item"><a class="page-link" href="'.$url.(($i*$topics_per_page)-$topics_per_page).$urlmore.'">'.$i.'</a></li>';
                        }
                    }
                }
                //  CAS 2 : au moins 13 pages -> troncature
                else 
                {
                    /* Troncature 1 : 1 2 3 4 5 6 7 8 9 … 16 17 */
                    if ($current < 2 + ($adj * 2)) 
                    {
                        $pagination .= ($current == 0) ? '
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>' : '<li class="page-item"><a class="page-link" href="'.$url.'0'.$urlmore.'">1</a></li>';
                        for ($i = 2; $i < 4 + ($adj * 2); $i++) 
                        {
                            if ($i == $current+1)
                            { 
                                $pagination .= '
                                <li class="page-item active"><a class="page-link" href="#">'.$i.'</a></li>';
                            }
                            else 
                            {
                                   $pagination .= '
                                   <li class="page-item"><a class="page-link" href="'.$url.(($i*$topics_per_page)-$topics_per_page).$urlmore.'">'.$i.'</a></li>';
                               }
                        }
                        
                        $pagination .= '
                        <li class="page-item disabled"><a class="page-link" href="#">&hellip;</a></li>
                        <li class="page-item"><a class="page-link" href="'.$url.(($penultimate*$topics_per_page)-$topics_per_page).$urlmore.'">'.$penultimate.'</a></li>
                        <li class="page-item"><a class="page-link" href="'.$url.(($total*$topics_per_page)-$topics_per_page).$urlmore.'">'.$total.'</a></li>';
                     }
                /* Troncature 2 : 1 2 … 5 6 7 8 9 10 11 … 16 17 */
                elseif ( (($adj * 2) + 1 < $current) && ($current < $total - ($adj * 2)) ) 
                {
                    $pagination .= '
                    <li class="page-item"><a class="page-link" href="'.$url.'0'.$urlmore.'">1</a></li>
                    <li class="page-item"><a class="page-link" href="'.$url.$topics_per_page.$urlmore.'">2</a></li>
                    <li class="page-item disabled"><a class="page-link" href="#">&hellip;</a></li>';
                    
                    // les pages du milieu : les trois précédant la page courante, 
                    // la page courante, puis les trois lui succédant
                    for ($i = ($current - $adj); $i <= $current + $adj; $i++) 
                    {
                        if ($i == $current+1) 
                        {
                            $pagination .= '
                            <li class="page-item active"><a class="page-link" href="#">'.$i.'</a></li>';
                        }
                        else 
                        {
                            $pagination .= '
                            <li class="page-item"><a class="page-link" href="'.$url.(($i*$topics_per_page)-$topics_per_page).$urlmore.'">'.$i.'</a></li>';
                        }
                    }
                    
                    $pagination .= '
                    <li class="page-item disabled"><a class="page-link" href="#">&hellip;</a></li>
                    <li class="page-item"><a class="page-link" href="'.$url.(($penultimate*$topics_per_page)-$topics_per_page).$urlmore.'">'.$penultimate.'</a></li>
                    <li class="page-item"><a class="page-link" href="'.$url.(($total*$topics_per_page)-$topics_per_page).$urlmore.'">'.$total.'</a></li>';
                }
                /* Troncature 3 : 1 2 … 9 10 11 12 13 14 15 16 17 */
                else 
                {
                    $pagination .= '
                    <li class="page-item"><a class="page-link" href="'.$url.'0'.$urlmore.'">1</a></li>
                    <li class="page-item"><a class="page-link" href="'.$url.$topics_per_page.$urlmore.'">2</a></li>
                    <li class="page-item disabled"><a class="page-link" href="#">&hellip;</a></li>';
                    
                    for ($i = $total - (2 + ($adj * 2)); $i <= $total; $i++) 
                    {
                        if ($i == $current+1) 
                        {
                            $pagination .= '
                            <li class="page-item active"><a class="page-link" href="#">'.$i.'</a></li>';
                        }
                        else 
                        {
                            $pagination .= '
                            <li class="page-item"><a class="page-link" href="'.$url.(($i*$topics_per_page)-$topics_per_page).$urlmore.'">'.$i.'</a></li>';
                        }
                    }
                }
            }
              
            if ($current+1 == $total)
            {
                $pagination .= '
                <li class="page-item disabled"><a class="page-link" href="#">►</a></li>';
            }
            else
            {
                $pagination .= '
                <li class="page-item"><a class="page-link" href="'.$url.$next.$urlmore.'" title="'.translate("Page suivante").'" data-toggle="tooltip">►</a></li>';
            }
            
            $pagination .= '
                </ul>
            </nav>';
        }
        
        return $pagination;
    }

}
