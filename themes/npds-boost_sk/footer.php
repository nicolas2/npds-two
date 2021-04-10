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
use npds\blocks\block;


/************************************************************************/
/* Fermeture ou ouverture et fermeture according with $pdst :           */
/*       col_LB +|| col_princ +|| col_RB                                */
/* Fermeture : div > div"#corps"> $ContainerGlobal>                     */
/*                    ouverts dans le Header.php                        */
/* =====================================================================*/ 
global $pdst;
switch ($pdst)
{
    case '-1':
    case '3':
    case '5':
        echo '
                </div>
            </div>
        </div>';
    break;

    case '1':
    case '2':
        echo '
            </div>';
        colsyst('#col_RB');
        echo '
            <div id="col_RB" class="collapse show col-lg-3 ">'."\n";
        block::rightblocks();
        echo '
                </div>
            </div>
        </div>';
   break;

   case '4':
        echo '
            </div>';
        colsyst('#col_LB');
        echo'
            <div id="col_LB" class="collapse show col-lg-3">'."\n";
        block::leftblocks();
        echo '
            </div>';
        colsyst('#col_RB');
        echo'
            <div id="col_RB" class="collapse show col-lg-3">'."\n";
        block::rightblocks();
        echo '
                </div>
            </div>
        </div>';
   break;

   case '6':
        echo '
        </div>';
        colsyst('#col_LB');
        echo'
            <div id="col_LB" class="collapse show col-lg-3">'."\n";
        block::leftblocks();
        echo '
                </div>
            </div>
        </div>';
   break;

   default:
        echo '
                </div>
            </div>
        </div>';
   break;
}

// ContainerGlobal permet de transmettre · Theme-Dynamic un élément de personnalisation après
// le chargement de footer.html / Si vide alors rien de plus n'est affiché par TD
$ContainerGlobal = '
</div>';

// Ne supprimez pas cette ligne / Don't remove this line
require_once("themes/default/footer.php");
// Ne supprimez pas cette ligne / Don't remove this line
