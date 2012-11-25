<?php if(count($cart->items) != 0) { ?>
	
	<form action='<?php echo $post_url ?>' method='post'>
	<table border='0' cellpadding='5' cellspacing='1' class='entryTable' align='center' width='96%'>	
		<thead>
		<tr class="thead">
			<th scope="col">Qty</th>
			<th scope="col">Items</th>
			<th scope="col" align="right">Unit Price</th>
		</tr>
		</thead>	
<?php
	$i=0;
		foreach($cart->get_contents() as $item) {

?>				
		  <tr class = "ck_content">
			<td>
            <input type='hidden' name='item_<?php  echo $i; ?>' value='<?php echo $item['id']; ?>' />
            <input type='text' name='qty_<?php  echo $i; ?>' size='2' value='<?php echo $item['qty'] ?>' class = 'qty_<?php  echo $item['id']; ?>' title='<?php echo $item['id']; ?>' /></td>
				<td><a href='<?php echo $item['url'] ?>'><strong><?php echo $item['name'] ?></strong><br />                
				<?php 
				if (count($item['options']) > 0){
				echo apply_filters(scabn_display_item_options,$item['options']);
				//echo scabn_Display::display_item_options($item['options']);
				} 
				?>
                
                </a></td>
				<td align='right'><?php echo $currency ?> <?php echo number_format($item['price'],2) ?><br />
<?php				
				$remove_query = array();
				$remove_query['remove'] = $item['id'];
				$remove_url = add_query_arg($remove_query);
				
?>				
				
				<a href='<?php echo $remove_url ?>' class ='remove_item' name = '<?php echo $item['id'] ?>'>Remove</a>
				</td>
				</tr>
<?php 

			$i ++;
			}

?>				
				<tr class='ck_content'>
				<td><input type='submit' name='update' value='Update' class ='update_cart' /></td>				
				<td align='right' colspan='1'>Sub-total</td>
				<td align='right'><?php echo $currency ?> <?php echo number_format($cart->total,2) ?></td>
				</tr>		
				
				<tr class='ck_content shipping'>
				<td align='right' colspan='2'>Shipping</td>
				<td align='right'>TBD</td>
				</tr>

               <?php   if (empty($cart_url)) { ?>
				 <span class='val_error'><strong>ERROR:</strong> Include the Checkout/Process Page Url on the Plugin Settings</span>
				<?php  } else {	 ?>
                                <tr><td class='ck_content go_to_checkout' colspan="3">
				 <div style="text-align: right"><span class='go_to_checkout'><a href='<?php echo $cart_url ?>'><strong>Go to Checkout</strong></a> </span></div> 
				<?php } ?>
				<tr class='ck_content total'>
				<td align='right' colspan='2'><strong>Total</strong></td>
				<td align='right'><strong><?php echo $currency ?> <?php echo number_format($cart->total,2) ?></strong></td>
				</tr>

                </td></tr>
                					   
        </table>
        </form>	
        
<?php 	} else {  ?>
	   
		<span class='no_items'>No items in your cart</span>
        
<?php  } ?>	
	



				  
