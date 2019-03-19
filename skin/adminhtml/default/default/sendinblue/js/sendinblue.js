/*
* @author Sendinblue plateform <contact@sendinblue.com>
* @copyright  2013-2014 Sendinblue
* URL:  https:www.sendinblue.com
* Do not edit or add to this file if you wish to upgrade Sendinblue Magento plugin to newer
* versions in the future. If you wish to customize Sendinblue magento plugin for your
* needs then we can't provide a technical support.
*/

jQuery.noConflict();
jQuery(document)
		.ready(
				function() {
				var apistatuskey = jQuery('#apistatuskey').val();

			    jQuery("#select").multiselect();

					jQuery(".sendin_api_status").click(function()
					{
						if (apistatuskey == 1 && jQuery(this).val() == 1)
						{
							jQuery("#sendin_apikey").show();
							jQuery(".alldiv").show();
							jQuery(".hidetableblock").show();
						}
						else{
						if(jQuery(this).val() == 1)
						{
							jQuery("#sendin_apikey").show();
						}else{
							jQuery("#sendin_apikey").hide();
							jQuery(".alldiv").hide();							
							}
						}						
					});


				//date picker function
            	jQuery('#sib_datetimepicker').datepicker({ dateFormat: 'yy-mm-dd' });
 				//Double optin function
				jQuery('.manage_subscribe_block input[name=subscribe_confirm_type]').click(function(){
				  jQuery('.manage_subscribe_block .inner_manage_box').slideUp(); 
				  jQuery(this).parents('.manage_subscribe_block').find('.inner_manage_box').slideDown();
				  
				});

               jQuery('.openCollapse').each(function(){
                if(!jQuery(this).is(":checked")){  
                	jQuery(this).parent('.form-group').find('.collapse').hide();
                }
                });

               jQuery('input[name=subscribe_confirm_type]').each(function(){               	
               	 if(jQuery(this).is(":checked")){
               	   jQuery(this).parents('.manage_subscribe_block').find('.inner_manage_box').show();	
               	 }
                });

				jQuery('.openCollapse').click(function() {
                     
                     if (jQuery(this).is(":checked")){ 
                          jQuery(this).parent('.form-group').find('.collapse').slideDown();
                     } else { 
                          jQuery(this).parent('.form-group').find('.collapse').slideUp();
                     }
                  });

					function loadData(page) {
					var ajaxcontentUrl = jQuery("#ajaxcontentUrl").val();
						jQuery.ajax({
							type : "POST",
							async : false,
							url : ajaxcontentUrl,
							data : "page=" + page,
							beforeSend : function() {
								jQuery('#ajax-busy').show();
							},
							success : function(msg) {
								jQuery('#ajax-busy').hide();
								jQuery(".midleft").html(msg);
								jQuery(".midleft").ajaxComplete(
								function(event, request, settings){
								
									jQuery(".midleft").html(msg);
									
								});
							}
						});
					}
					
					jQuery('.hdtab').click(function(){
						if(parseInt(jQuery(this).val())) {	jQuery('#hidetabselect').css('display','block');}						
					});

					jQuery('body').on('click',' .pagination li.active',function() {
								var page = jQuery(this).attr('p');
								jQuery('#pagenumber').val(page);
								loadData(page);
							});

				jQuery('.toolTip').hover(function () {
                var title = jQuery(this).attr('title');
                var offset = jQuery(this).offset();

                jQuery('body').append(
                    '<div id="tipkk" style="top:' + offset.top + 'px; left:' + offset.left + 'px; ">' + title + '</div>');
                var tipContentHeight = jQuery('#tipkk')
                    .height() + 25;
                jQuery('#tipkk').css(
                    'top', (offset.top - tipContentHeight) + 'px');

            }, function () {
                jQuery('#tipkk').remove();
            });
			jQuery('#sender_order').mouseover(function () {
                 var val = jQuery(this).val();
                if(isInteger(val) || val == ''){
					jQuery("#sender_order").attr('maxlength','11');
					jQuery('#sender_order_text').text((11 - val.length));
					 
				}
				else{
					jQuery("#sender_order").attr('maxlength','11');
					var str7 = val.length > 11 ? val.substr(1,11) : val;						
					jQuery("#sender_order").val(str7);
					jQuery('#sender_order_text').text((11 - val.length));
				}
            });
 
            jQuery('#sender_order').keyup(function () {
                var val = jQuery(this).val();
                if(isInteger(val) || val == ''){
					jQuery("#sender_order").attr('maxlength','11');
					jQuery('#sender_order_text').text((11 - val.length));
					 
				}
				else{
					jQuery("#sender_order").attr('maxlength','11');
					var str_val = val.length > 11 ? val.substr(1,11) : val;						
					jQuery("#sender_order").val(str_val);
					jQuery('#sender_order_text').text((11 - val.length));	
					 
				}
            });
			
			jQuery("#sender_order").keydown(function (event) {
			if (event.keyCode == 32) {
				event.preventDefault();
                }
				});	
             
            var sender_order = jQuery('#sender_order');
			var sender_order_val = sender_order.val();
            if(sender_order_val)
			{
				if(isInteger(sender_order_val)){
						jQuery("#sender_order").attr('maxlength','11');
						jQuery('#sender_order_text').text((11 - sender_order_val.length));
						 
					}
					else{
						jQuery("#sender_order").attr('maxlength','11');
						jQuery('#sender_order_text').text((11 - sender_order_val.length));
					}
			}

            jQuery('#sender_order_message').keyup(function () {
                var chars = this.value.length,
				messages = Math.ceil(chars / 160),
				remaining = messages * 160 - chars;
				
				jQuery('#sender_order_message_text').text(remaining);

                jQuery('#sender_order_message_text_count').text(messages);
            });
            var sender_order_message = jQuery('#sender_order_message');
            var sender_order_message_val = sender_order_message.val();
			if(sender_order_message_val)
			{
				var chars = sender_order_message_val.length,
				messages = Math.ceil(chars / 160),
				remaining = messages * 160 - chars;
				
				jQuery('#sender_order_message_text').text(remaining);

                jQuery('#sender_order_message_text_count').text(messages);
			}

			jQuery('#sender_shipment').mouseover(function () {
                var val = jQuery(this).val();
                
                 if(isInteger(val) || val == ''){
					jQuery("#sender_shipment").attr('maxlength','11');
					jQuery('#sender_shipment_text').text((11 - val.length));					 
				}
				else{
					jQuery("#sender_shipment").attr('maxlength','11');
					var str7 = val.length > 11 ? val.substr(1,11) : val;						
					jQuery("#sender_shipment").val(str7);
					jQuery('#sender_shipment_text').text((11 - val.length));					 
				}
            });
			jQuery('#sender_shipment').keyup(function () {
                var val = jQuery(this).val();                
                 if(isInteger(val) || val == ''){
					jQuery("#sender_shipment").attr('maxlength','11');
					jQuery('#sender_shipment_text').text((11 - val.length));					 
				}
				else{
					jQuery("#sender_shipment").attr('maxlength','11');
					var str7 = val.length > 11 ? val.substr(1,11) : val;						
					jQuery("#sender_shipment").val(str7);
					jQuery('#sender_shipment_text').text((11 - val.length));				 
				}
            });
			jQuery("#sender_shipment").keydown(function (event) {
					if (event.keyCode == 32) {
                    event.preventDefault();
                }
				});

             if(jQuery('#sender_shipment').val() != '')
			{
				var sender_shipment = jQuery('#sender_shipment');
				var sender_shipment_val = sender_shipment.val();
				if (sender_shipment_val) {
					if(isInteger(sender_shipment_val)){
						jQuery("#sender_shipment").attr('maxlength','11');
						jQuery('#sender_shipment_text').text((11 - sender_shipment_val.length));					 
					}
					else{
						jQuery("#sender_shipment").attr('maxlength','11');
						jQuery('#sender_shipment_text').text((11 - sender_shipment_val.length));					 
					}
				}
			}

            jQuery('#sender_shipment_message').keyup(function () {
				
				var chars = this.value.length,
				messages = Math.ceil(chars / 160),
				remaining = messages * 160 - chars;

				jQuery('#sender_shipment_message_text').text(remaining);
                jQuery('#sender_shipment_message_text_count').text(messages);                
            });

			var sender_shipment_message = jQuery('#sender_shipment_message');
			var sender_shipment_message_val = sender_shipment_message.val()
            if(sender_shipment_message_val)
			{
				var chars = sender_shipment_message_val.length,
				messages = Math.ceil(chars / 160),
				remaining = messages * 160 - chars;
				
				jQuery('#sender_shipment_message_text').text(remaining);
                jQuery('#sender_shipment_message_text_count').text(messages);
			}

			jQuery('#sender_campaign').mouseover(function () {
                var val = jQuery(this).val();				              
                if(isInteger(val) || val == ''){ 
					jQuery("#sender_campaign").attr('maxlength','11');
					jQuery('#sender_campaign_text').text((11 - val.length));
					 
				}
				else{
					jQuery("#sender_campaign").attr('maxlength','11');
					var str7 = val.length > 11 ? val.substr(1,11) : val;						
					jQuery("#sender_campaign").val(str7);
					jQuery('#sender_campaign_text').text((11 - val.length));	
					 
				}
            });
			jQuery('#sender_campaign').keyup(function () {
                var val = jQuery(this).val();
                
                if(isInteger(val) || val == ''){
					jQuery("#sender_campaign").attr('maxlength','11');
					jQuery('#sender_campaign_text').text((11 - val.length));					 
				}
				else{
					jQuery("#sender_campaign").attr('maxlength','11');
					var str7 = val.length > 11 ? val.substr(1,11) : val;						
					jQuery("#sender_campaign").val(str7);
					jQuery('#sender_campaign_text').text((11 - val.length));
				}
            });
			jQuery("#sender_campaign").keydown(function (event) {
			if (event.keyCode == 32) {
                event.preventDefault();
                }
				});

			var sender_campaign = jQuery('#sender_campaign');
			var sender_campaign_val = sender_campaign.val();
            if(sender_campaign_val)
			{				
				if(isInteger(val)){
					jQuery("#sender_campaign").attr('maxlength','11');
					jQuery('#sender_campaign_text').text((11 - sender_campaign_val.length));
				}
				else{
					jQuery("#sender_campaign").attr('maxlength','11');
					jQuery('#sender_campaign_text').text((11 - sender_campaign_val.length));
				}
			}
            
            jQuery('#sender_campaign_message').keyup(function () {
                
                var chars = this.value.length,
				messages = Math.ceil(chars / 160),
				remaining = messages * 160 - (chars % (messages * 160) || messages * 160);

				jQuery('#sender_campaign_message_text').text(remaining);
                jQuery('#sender_campaign_message_text_count').text(messages);              
            });

			var sender_campaign_message = jQuery('#sender_campaign_message');
			var sender_campaign_message_val = sender_campaign_message.val();
            if(sender_campaign_message_val)
			{
				var chars = sender_campaign_message_val.length,
				messages = Math.ceil(chars / 160),
				remaining = messages * 160 - (chars % (messages * 160) || messages * 160);
				
				jQuery('#sender_campaign_message_text').text(remaining);
                jQuery('#sender_campaign_message_text_count').text(messages);
			}

			jQuery(".sms_order_setting").click(function () {
				    var orderSetting = jQuery(this).val();
				    var orderUrl = jQuery("#order").val();
				    
				    jQuery.ajax({
                    type: "POST",
                    async: false,
                    url: orderUrl,
                    data: "orderSetting=" + orderSetting,
                    beforeSend: function () {
                        jQuery('#ajax-busy').show();
                    },
                    success: function (msg) {
                        jQuery('#ajax-busy').hide();
						if (orderSetting == 1) {
							jQuery(".hideOrder").show();
						} else {
							jQuery(".hideOrder").hide();
						}
                    }
                });				
			});

			jQuery(".sms_shiping_setting").click(function () {
				    var shipingSetting = jQuery(this).val();
				    var shipingUrl = jQuery("#shiping").val();

				    jQuery.ajax({
                    type: "POST",
                    async: false,
                    url: shipingUrl,
                    data: "shipingSetting=" + shipingSetting,
                    beforeSend: function () {
                        jQuery('#ajax-busy').show();
                    },
                    success: function (msg) {
                        jQuery('#ajax-busy').hide();
						if (shipingSetting == 1) {
							jQuery(".hideShiping").show();
						} else {
							jQuery(".hideShiping").hide();
						}
                    }
                });
			});
			
			jQuery(".sms_campaign_setting").click(function () {
				    var campaignSetting = jQuery(this).val();
				    var campaignUrl = jQuery("#campaign").val();
				    
				    jQuery.ajax({
                    type: "POST",
                    async: false,
                    url: campaignUrl,
                    data: "campaignSetting=" + campaignSetting,
                    beforeSend: function () {
                        jQuery('#ajax-busy').show();
                    },
                    success: function (msg) {
                        jQuery('#ajax-busy').hide();
						if (campaignSetting == 1) {
							jQuery(".hideCampaign").show();
						} else {
							jQuery(".hideCampaign").hide();
						}
                    }
                });				
			});

            if (jQuery('input:radio[name=sms_order_setting]:checked').val() == 0)
            {
                jQuery('.hideOrder').hide();
            } else {
                jQuery('.hideOrder').show();
            }
            
            jQuery(".Sendin_Sms_Choice").click(function ()
            {
				if (jQuery(this).val() == 1) {
					jQuery(".multiplechoice").hide();
					jQuery(".singlechoice").show();
				} else {
					jQuery(".multiplechoice").show();
					jQuery(".singlechoice").hide();
				}
			});
			jQuery(".Sendin_Sms_Choice").click(function ()
            {
				if (jQuery(this).val() == 2) {
					jQuery(".sib_datepicker").show();
				} else {
					jQuery(".sib_datepicker").hide();
				}
			});
			
			if (jQuery('input:radio[name=Sendin_Sms_Choice]:checked').val() == 0)
			{
                jQuery(".multiplechoice").show();
                jQuery(".singlechoice").hide();
            } else {
                jQuery(".singlechoice").show();
                jQuery(".multiplechoice").hide();
            }
			
            jQuery(".sms_shiping_setting").click(function () {
				if (jQuery(this).val() == 1) {
					
					jQuery(".hideShiping").show();
				} else {
					jQuery(".hideShiping").hide();
				}
			});			

			if (jQuery('input:radio[name=sms_credit]:checked').val() == 0)
                jQuery(".hideCredit").hide();
            else
                jQuery(".hideCredit").show();

            jQuery(".sms_credit").click(function (){
				
				 var sms_credit = jQuery(this).val();
				  var creditUrl = jQuery("#credits").val();
				    var type = 'sms_credit';
				    jQuery.ajax({
                    type: "POST",
                    async: false,
                    url: creditUrl,
                    data: "sms_credit=" + sms_credit,
                    beforeSend: function () {
                        jQuery('#ajax-busy').show();
                    },
                    success: function (msg) {
                        jQuery('#ajax-busy').hide();
						if (sms_credit == 1) {
							jQuery(".hideCredit").show();
						} else {
							jQuery(".hideCredit").hide();
						}
                    }
                });
				
			});
			
            if (jQuery('input:radio[name=sms_shiping_setting]:checked').val() == 0) {
                jQuery('.hideShiping').hide();
            } else {
                jQuery('.hideShiping').show();
            }
            
             jQuery(".sms_campaign_setting").click(function () {
				if (jQuery(this).val() == 1) {
					jQuery(".hideCampaign").show();
				} else {
					jQuery(".hideCampaign").hide();
				}
			});
		
            if (jQuery('input:radio[name=sms_campaign_setting]:checked').val() == 0) {
                jQuery('.hideCampaign').hide();
            } else {
                jQuery('.hideCampaign').show();
            }
            
            jQuery("#selectSmsList").multiselect({
			header: false,
			checkall:false
		});
		
		jQuery("#tabs li").click(function() {
			//	First remove class "active" from currently active tab
			jQuery("#tabs li").removeClass('active');

			//	Now add class "active" to the selected/clicked tab
			jQuery(this).addClass("active");

			//	Hide all tab content
			jQuery(".tab_content").hide();

			//	Here we get the href value of the selected tab
			var selected_tab = jQuery(this).find("a").attr("href");

			//	Show the selected tab content
			jQuery(selected_tab).fadeIn();

			//	At the end, we add return false so that the click on the link is not executed
			return false;
		});
	 	
	function isInteger(val) {
			var numberRegex = /^[+-]?\d+(\.\d+)?([eE][+-]?\d+)?$/;
			if(numberRegex.test(val)) {
				return true
			}
			return false;
	}	

	jQuery('#showUserlist').click(function(){

		if(jQuery('.userDetails').is(':hidden'))
		{
			loadData(1);
			jQuery('#Spantextless').show();
			jQuery('#Spantextmore').hide();
		}else
		{
			jQuery('#Spantextmore').show();
			jQuery('#Spantextless').hide();
		}
		jQuery('.userDetails').slideToggle();
	});
					
	var base_url = getBaseURL();

	jQuery(".Tracking").click(function()
	{
		var Tracking = jQuery(this).val();
		var trackingUrl = jQuery("#trackingUrl").val();
								if (Tracking == 0) {
			jQuery('.ordertracking').hide();
		}
		if (Tracking == 1) {
			jQuery('.ordertracking').show();
		}
		jQuery.ajax({
			type : "POST",
			async : false,
			url : trackingUrl,
			data : "script=" + Tracking,
			beforeSend : function(){
				jQuery('#ajax-busy').show();
			},
			success : function(msg){
				jQuery('#ajax-busy').hide();
			}
		});
	});

//for import old order history  
jQuery(".Trackhistory").click(function()
{
var history_status = jQuery("#history_status").val();
var langvalue = jQuery("#langvalue").val();
var ordertrackingUrl = jQuery("#importordertrackingUrl").val();
																		
jQuery.ajax({
	type : "POST",
	async : false,
	url : ordertrackingUrl,
	data : {"history_status":history_status,"langvalue":langvalue},
	beforeSend : function(){
		jQuery('#ajax-busy').show();
	},
	success : function(msg){
		jQuery('#ajax-busy').hide();
		jQuery('.ordertracking').hide();
		alert(msg);
	}
});
});

jQuery(".smtpStatus").click(function() {

var smtptest = jQuery(this).val();
var smtpUrl = jQuery("#smtpUrl").val();
if (smtptest == 0) {
	jQuery('.smtptest').hide();
}
if (smtptest == 1) {
	jQuery('.smtptest').show();
}
jQuery.ajax({
	type : "POST",
	async : false,
	url : smtpUrl,
	data : "smtptest=" + smtptest,
	beforeSend : function() {
		jQuery('#ajax-busy').show();
	},
	success : function(msg) {
		jQuery('#ajax-busy').hide();
	}
});
});
jQuery(".AutomationSubmit").click(function() {
var automationEnableStatus = jQuery("input[name='Automation']:checked").val();
var automationUrl = jQuery("#automationUrl").val();
var automsg = jQuery('#automsg').val();

if (automationEnableStatus == 0) {
    var resp = confirm(automsg);
    if (resp === false) {
        return;
    }
}
jQuery.ajax({
    type : "POST",
    async : false,
    url : automationUrl,
    data : "script=" + automationEnableStatus,
    beforeSend : function(){
        jQuery('#ajax-busy').show();
    },
    success : function(msg){
        jQuery('#ajax-busy').hide();
    }
});
});
var skin_url = jQuery('#skin_url').val()
	jQuery('<div id="ajax-busy"/> loading..')
	.css(
			{
				opacity : 0.5,
				position : 'fixed',
				top : 0,
				left : 0,
				width : '100%',
				height : jQuery(window).height() + 'px',
				background : 'white url('+skin_url+'adminhtml/default/default/sendinblue/images/loader.gif) no-repeat center'
			}).hide().appendTo('body');

// get site base url							
function getBaseURL() {
	var sBase = location.href.substr(0, location.href.lastIndexOf("/") + 1);
	var sp = sBase.split('/');
	var lastFolder = sp[ sp.length - 2 ];
	return sBase.replace(lastFolder+'/', '');
}

jQuery('body').on('click', '.ajax_contacts_href', function (e) {
	var email = jQuery(this).attr('email');
	var status = jQuery(this).attr('status');
	var ajaxUrl = jQuery("#ajaxUrl").val();

	jQuery.ajax({
			type : "POST",
			async : false,
			url : ajaxUrl,
			data : {"email":email,"newsletter":status},
			beforeSend : function() {
				jQuery('#ajax-busy').show();
			},
			success : function(msg) {
				jQuery('#ajax-busy').hide();			
			}
		});

	var page_no = jQuery('#pagenumber').val();		
	loadData(page_no); // For first time page load		
});

jQuery('body').on('click', '.ajax_sms_subs_href', function (e) {

var email = jQuery(this).attr('email');
var status = jQuery(this).attr('status');
var ajaxSmsUrl = jQuery("#ajaxSmsSubscribeUrl").val();

jQuery.ajax({
		type : "POST",
		async : false,
		url : ajaxSmsUrl,
		data : {"email":email,"sms":status},
		beforeSend : function() {
			jQuery('#ajax-busy').show();
		},
		success : function(msg) {
			jQuery('#ajax-busy').hide();			
		}
	});

var page_no = jQuery('#pagenumber').val();		
loadData(page_no); // For first time page load

});
});

function testsmssend(sendererr,messageerr,mobileerr) { 
	var sender = jQuery('#sender_order').val();		
	var message =jQuery('#sender_order_message').val();
	var number = jQuery("#sender_order_number").val();
	var ajaxOrderSmsUrl = jQuery('#ajaxOrderSmsUrl').val();
	var smsCampError = jQuery("#smsCampError").val();
	var smsCampSuccess = jQuery("#smsCampSuccess").val();
	if(sender == '' || isValid(sender) == false)
	{
		alert(sendererr);
	} 
	else if( message == '')
	{
		alert(messageerr);
	}
	else if(number == '')
	{
		alert(mobileerr);
	}
	else {
	jQuery.ajax({
		type : "POST",
		async : false,
		url : ajaxOrderSmsUrl,				
		data : {"sender":sender,"message":message,"number":number} ,
		beforeSend : function() {
			jQuery('#ajax-busy').show();
		},
		success : function(msg) {
			jQuery('#ajax-busy').hide();
			if(msg.trim() == 'OK')
			{ alert(smsCampSuccess); }
			else { alert(smsCampError); }
		}
	});}
	return false;
}

function testShippedSmsSend(sendererr,messageerr,mobileerr) {
	var sender = jQuery('#sender_shipment').val();		
	var message =jQuery('#sender_shipment_message').val();
	var number = jQuery("#sender_shipment_number").val();
	var ajaxOrderShippedUrl = jQuery('#ajaxOrderShippedUrl').val();
	var smsCampError = jQuery("#smsCampError").val();
	var smsCampSuccess = jQuery("#smsCampSuccess").val();
	if(sender == '' || isValid(sender) == false)
	{
		alert(sendererr);
	} 
	else if( message == '')
	{
		alert(messageerr);
	}
	else if(number == '')
	{
		alert(mobileerr);
	}
	else {
	jQuery.ajax({
		type : "POST",
		async : false,
		url : ajaxOrderShippedUrl,				
		data : {"sender":sender,"message":message,"number":number} ,
		beforeSend : function() {
			jQuery('#ajax-busy').show();
		},
		success : function(msg) {
			jQuery('#ajax-busy').hide();
			if(msg.trim() == 'OK')
			{ alert(smsCampSuccess); }
			else { alert(smsCampError); }
		}
	}); }
	return false;
}

function testCampaignSmsSend(sendererr,messageerr,mobileerr) { 

	var sender = jQuery('#sender_campaign').val();		
	var message =jQuery('#sender_campaign_message').val();
	var number = jQuery("#sender_campaigntest_number").val();
	var ajaxSmsCampaignUrl = jQuery('#ajaxSmsCampaignUrl').val();
	var smsCampError = jQuery("#smsCampError").val();
	var smsCampSuccess = jQuery("#smsCampSuccess").val();
	
	if(sender == '' || isValid(sender) == false)
	{
		alert(sendererr);
	} 
	else if( message == '')
	{
		alert(messageerr);
	}
	else if(number == ''  || isMobilevalidation(number) == false)
	{
		alert(mobileerr);
	}
	else {
	jQuery.ajax({
		type : "POST",
		async : false,
		url : ajaxSmsCampaignUrl,				
		data : {"sender":sender,"message":message,"number":number} ,
		beforeSend : function() {
			jQuery('#ajax-busy').show();
		},
		success : function(msg) { 
			jQuery('#ajax-busy').hide();
			if(msg.trim() == 'OK')
			{ alert(smsCampSuccess); }
			else { alert(smsCampError); }
		}
	});}
	return false;
}

function senderOrderSaveValid(sendererr,messageerr)
{
	var sender = jQuery('#sender_order').val();		
    var message =jQuery('#sender_order_message').val();
	if(sender == '' || isValid(sender) == false)
	{
		alert(sendererr);
		return false;
	} 
	else if( message == '')
	{
		alert(messageerr);
		return false;
	}
}

function senderShipmentSaveValid(sendererr,messageerr)
{
	var sender = jQuery('#sender_shipment').val();		
    var message =jQuery('#sender_shipment_message').val();
	if(sender == '' || isValid(sender) == false)
		{
			alert(sendererr);
			return false;
		} 
		else if( message == '')
		{
			alert(messageerr);
			return false;
		}
}

function senderCampaignSaveValid(sendererr,messageerr,mobileerr)
{
	var sender = jQuery('#sender_campaign').val();		
	var message =jQuery('#sender_campaign_message').val();
	var number = jQuery("#singlechoice").val();		
	var radiovalue = jQuery("input[name=Sendin_Sms_Choice]:checked").val();
	if(radiovalue == 1)
	{
	if(number == '' || isMobilevalidation(number) == false)
	{
		alert(mobileerr);
		return false;
	} 
	else if(sender == '' || isValid(sender) == false)
	{
		alert(sendererr);
		return false;
	}
	else if(message == '')
	{
		alert(messageerr);
		return false;
	}
	}
	else{
	if(sender == '' || isValid(sender) == false)
	{
		alert(sendererr);
		return false;
	}
	else if(message == '')
	{
		alert(messageerr);
		return false;
	}
	}
}

function isMobilevalidation(str)
{
     return /^(?:\+|00)[1-9][0-9]{5,15}$/.test(str);
}
	
function isNormalInteger(str)
{
     return /^\+?(0|[1-9]\d*)$/.test(str);
}

function isValid(str) {
    var iChars = "~`!#$%^&*+=-[]\\\';,/{}|\":<>?";

    for (var i = 0; i < str.length; i++) {
       if (iChars.indexOf(str.charAt(i)) != -1) {        
           return false;
       }
    }	
    return true;
}

function RegexEmail(email)
{    
  var emailRegexStr = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
  var isvalid = emailRegexStr.test(email); 
  return isvalid;
}

function validate(emailerr,limiter)
 {
   if( document.notify_sms_mail_form.sendin_notify_email.value == "" || RegexEmail(document.notify_sms_mail_form.sendin_notify_email.value) == false )
   {
     alert(emailerr);
     document.notify_sms_mail_form.sendin_notify_email.focus() ;
     return false;
   }
   if( document.notify_sms_mail_form.sendin_notify_value.value == "" ||
           isNormalInteger( document.notify_sms_mail_form.sendin_notify_value.value)== false )
   {
     alert(limiter);
     document.notify_sms_mail_form.sendin_notify_value.focus() ;
     return false;
   }

   return( true );
 }

function smtpvalidate(emailerr)
{
  var email = jQuery('#email').val();
   if(email == "" || RegexEmail(email) == false )
   {
     alert(emailerr);
     jQuery('#email').focus();
     return false;
   }
}
	
function apikvalidate(apierr)
{
	var sendin_apikey_val = jQuery('#sendin_apikey_val').val();
	var sendin_api_check = jQuery("input[name=sendin_api_status]:checked").val();

   if(sendin_apikey_val.trim() == "" && sendin_api_check !=0)
   {
	alert(apierr);
	jQuery('#sendin_apikey_val').focus();
	return false;
   }  
}	
