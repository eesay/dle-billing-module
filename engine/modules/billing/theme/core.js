/*
=====================================================
 Billing
-----------------------------------------------------
 evgeny.tc@gmail.com
-----------------------------------------------------
 This code is copyrighted
=====================================================
*/
					
var users = [];

function usersSelectSend()
{
	if( $( "#edit_group option:selected" ).text() )
	{
		$("#edit_comm").prop('disabled', true);
		$("#edit_comm").val('Недоступно');
	}
	else
	{
		$("#edit_comm").prop('disabled', false);
		$("#edit_comm").val('');
	}
}

function showEditDate( tag, tag_to )
{
	$(tag).hide();
	$(tag_to).show();
	$("#EditDateButton").show();
}

function checkAll(obj)
{
	var items = obj.form.getElementsByTagName("input"), len, i;
						
	for (i = 0, len = items.length; i < len; i += 1)
	{
		if (items.item(i).type && items.item(i).type === "checkbox")
		{          
			if (obj.checked)
			{
				items.item(i).checked = true;
			}
			else 
			{
				items.item(i).checked = false;
			}  
		}
	}
};
					
function usersAdd( name )
{
	if( users.in_array(name) )
	{
		users.clean(name);
		$('#user_'+name).html('<i class=\"icon-plus\" style=\"margin-left: 10px; vertical-align: middle\"></i>');
	} 
	else 
	{
		users[users.length+1] = name;
		$('#user_'+name).html('<span class=\'status-success\'><b><i class=\'icon-plus\' style=\'margin-left: 10px; vertical-align: middle\'></i></b></span>');
	}	

	users.clean(undefined);
	
	$('#edit_name').val( users.join(', ') );
};

function ShowOrHideCookie( id )
{
	var item = $("#" + id);

	var scrolltime = (item.height() / 200) * 1000;

	if (scrolltime > 3000 ) { scrolltime = 3000; }

	if (scrolltime < 250 ) { scrolltime = 250; }

	if (item.css("display") == "none") { 

		$.cookie('cookie_'+id, 'show', { expires: 7 });
	
		item.show('blind',{}, scrolltime );

	} else {

		$.cookie('cookie_'+id, null);
	
		if (scrolltime > 2000 ) { scrolltime = 2000; }

		item.hide('blind',{}, scrolltime );
	}

};

function ReportClose()
{
	$("#report_panel").remove();
	
	$.cookie('report_panel', 'close', { expires: 7 });
}

Array.prototype.in_array = function(p_val)
{
	for(var i = 0, l = this.length; i < l; i++)
	{
		if(this[i] == p_val)
		{
			return true;
		}
	}
	return false;
};

Array.prototype.clean = function(deleteValue)
{
    for (var i = 0; i < this.length; i++)
    {
        if (this[i] == deleteValue)
        {         
            this.splice(i, 1);
            i--;
        }
    }
    return this;
};