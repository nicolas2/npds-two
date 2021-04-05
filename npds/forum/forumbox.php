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
namespace npds\forum;


/*
 * forumbox
 */
class forumbox {


    /**
     * [searchblock description]
     * @return [type] [description]
     */
    public static function searchblock() 
    {
        $ibid = '
        <div class="card d-flex flex-row-reverse p-1">
            <form class="form-inline" id="searchblock" action="searchbb.php" method="post" name="forum_search">
                <input type="hidden" name="addterm" value="any" />
                <input type="hidden" name="sortby" value="0" />
                <div class="">
                    <label class="sr-only" for="term">'.translate('Recherche').'</label>
                    <input type="text" class="form-control" name="term" id="term" placeholder="'.translate('Recherche').'">
                </div>
                <div class=" ml-2">
                    <button type="submit" class="btn btn-outline-primary">'.translate("Valider").'</button>
                </div>
            </form>
        </div>';

        return $ibid;
    }

}
