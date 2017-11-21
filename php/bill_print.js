        
        var settings = {
          output:"css",
          bgColor: "#FFFFFF",
          color: "#000000",
          barWidth: 1,
          barHeight: 25,
          moduleSize: 5,
          posX: 0,
          posY: 0,
          addQuietZone: 1
        };

        var btype = "code128";
//        var barcode_print = <?php echo "$barcode_print" ?>; 
//        var qr_print = <?php echo "$qr_print" ?>; 
        
        jQuery(function(){ 
            if (barcode_print==1)
            {
              $('.barcode_area').each(function() {
                  $(this).html("").show().barcode($(this).attr('data_value'), btype, settings);
              });
            }
            
        });