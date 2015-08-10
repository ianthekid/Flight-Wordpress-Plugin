jQuery(document).ready(function($) {
	var offsetBold = "";
	var offsetPaold = "";
	var offsetProld = "";
	var offsetWebold = "";
	function loadMore(response) {
		
		console.log(response);
		if(response) {
			
			var di = '';
			
			for(var i=0; i<response.length; i++) {
				di += '<div class="col-sm-6 col-md-4 post_tile '+response[i].taxCat+'" onclick="window.location=\''+response[i].theLink+'\'"><article style="background: url('+response[i].imgURL+');"></article><div><p class="tax-cat"><span class="box-'+response[i].color+'">'+response[i].taxName+'</span></p><h4>'+response[i].title+'</h4><aside><p class="pull-right"><i class="fa fa-bookmark"></i> <time class="updated" datetime="'+response[i].timeC+'">'+response[i].time+'</time></p><p class="pull-left"><a href="'+response[i].theLink+'" class="btn btn-white">Read More</a></p></aside></div></div>';
				
				di += '</div></div>';
			}
			newItems = $(di).appendTo('#post_tiles');
			$container.isotope( 'appended',newItems );
	
			$('#ajax-loader').css('display','none');
		} else {
			$('#load_more').remove();
		}		
	}
	
	
	var $container = $('#post_tiles'),
		$select = $('#filters2 a');
		$button = $('#load_more');
		
	if(window.location.hash)
	{
		var url = window.location.href;
		var entranceFilter = "."+url.substring(url.indexOf("#")+1);
		$container.isotope({ filter:entranceFilter});
	}
	
	$container.isotope({
		itemSelector: '.post_tile'
	});


	$buttons = $('#filters2 a');
	
	$buttons.click(function(){
		var filters = $(this).data('cat');
		console.log(filters);
		$container.isotope ({ filter:filters});
	});

	
	$button.click( function() {
		
		$('#ajax-loader').css('display','inline-block');
		var offsetbook = parseInt($(this).data('offsetbook'));
		var offsetpaper = parseInt($(this).data('offsetpaper'));
		var offsetproduct = parseInt($(this).data('offsetproduct'));
		var offsetweb = parseInt($(this).data('offsetweb'));
		var offset = parseInt($(this).data('offset'));
		var paged = parseInt($(this).data('paged'));
		var offsetbdiff = "";
		var offsetpadiff = "";
		var offsetprodiff = "";
		var offsetwediff = "";
		
		console.log(offsetbook);
		console.log(offsetweb);
		//Set Difference of Offsets
		//ebooks
		if(offsetBold == "")
		{
			offsetBold = offsetbook;
			offsetbdiff = offsetBold;
		}
		else
		{
			offsetbdiff = offsetbook - offsetBold;
			offsetBold = offsetbook;
		}
		
		//White-papers
		if(offsetPaold == "")
		{
			offsetPaold = offsetpaper;
			offsetpadiff = offsetPaold;
		}
		else
		{
			offsetpadiff = offsetpaper - offsetPaold;
			offsetPaold = offsetpaper;
		}
		
		//Product-information
		if(offsetProld == "")
		{
			offsetProld = offsetproduct;
			offsetprodiff = offsetProld;
		}
		else
		{
			offsetprodiff = offsetproduct - offsetProld;
			offsetProld = offsetproduct;
		}
		
		//Webinars
		if(offsetWebold == "")
		{
			offsetWebold = offsetweb;
			offsetwediff = offsetWebold;
		}
		else
		{
			offsetwediff = offsetweb - offsetWebold;
			offsetWebold = offsetweb;
		}
		
		
		
		// Information of our Request
		var data = {
			'action' : 'inventory_posts',
			'offset' : offset,
			'offsetbook' : offsetbook,
			'offsetpaper' : offsetpaper,
			'offsetproduct' : offsetproduct,
			'offsetweb' : offsetweb,
			'paged' : paged,
			
		};
		$(this).data('offsetbook',offsetbook+offsetbdiff);
		$(this).data('offsetweb',offsetweb+offsetwediff);
		$(this).data('offsetpaper',offsetpaper+offsetpadiff);
		$(this).data('offsetproduct',offsetproduct+offsetprodiff);
		$(this).data('paged',paged+1);
		console.log(offsetbook);
		console.log(offsetweb);
		$.post(myAjax.ajaxurl, data, function(response) {
			loadMore(response);
		}, 'json');
		
	});
	
});
