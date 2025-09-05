<?php
namespace common\widgets;

use thrieu\grid\FilterStateInterface;
use thrieu\grid\FilterStateTrait;

class GridView extends \kartik\grid\GridView implements FilterStateInterface {
    use FilterStateTrait;
}
?>