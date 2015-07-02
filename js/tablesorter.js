			jQuery(document).ready(function($)
				{ 
					jQuery(".tblSchedule")
						.tablesorter( {sortList: [[1,0], [2,0]], widgets: ["zebra"] })
						.bind("sortEnd", function(){
			    		jQuery(".tblSchedule").trigger("applyWidgets"); 
						});
					} 
				);