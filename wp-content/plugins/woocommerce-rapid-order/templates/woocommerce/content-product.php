<?php
// Simply using the default behaviour of most themes (looping through products on archive pages) to collect
// products into an array. We'll dump everything out to a javascript variable in loop/loop-end.php
WC_Rapid_Order::instance()->loop->the_product();