document.addEventListener('DOMContentLoaded',()=>{
    const bookingFrom = document.getElementById('booking_from');
    const bookingTo = document.getElementById('booking_to');
    jQuery('#date').datepicker(
        {
            dateFormat: 'dd/mm/yy',
            beforeShowDay: unavailable
        }
    );

    bookingFrom.addEventListener('change',()=>{
       
        if(bookingFrom.value == 'toronto'){
            for(let i = 0 ; bookingTo.length; i++){
                if(bookingTo.options[i].value == "nanaimo"){
                    bookingTo.options[i].selected = "true";
                    break;
                }
            }
        }
        else if(bookingFrom.value == 'nanaimo'){
            for(let i = 0 ; bookingTo.length; i++){
                if(bookingTo.options[i].value == "toronto"){
                    bookingTo.options[i].selected = "true";
                    break;
                }
            }
        }
        else{
            bookingTo.options[0].selected = "true";
        }
        
    })

    bookingTo.addEventListener('change',()=>{
        if(bookingTo.value == 'toronto'){
            for(let i = 0; bookingFrom.length ; i++){
                if(bookingFrom.options[i].value == "nanaimo"){
                    bookingFrom.options[i].selected = "true";
                    break;
                }
            }
        }
        else if(bookingTo.value == "nanaimo"){
            for(let i = 0; bookingFrom.length ; i++){
                if(bookingFrom.options[i].value == 'toronto'){
                    bookingFrom.options[i].selected = 'true';
                    break;
                }
            }
        }
        else{
            bookingFrom.options[0].selected = "true";
        }
    })

    console.log(unavailableDates);
    
    function unavailable(date) {
        // Format the date as dd/mm/yyyy
        var day = ("0" + date.getDate()).slice(-2);  // Pad day with leading zero if needed
        var month = ("0" + (date.getMonth() + 1)).slice(-2);  // Pad month with leading zero
        var year = date.getFullYear();
        var formattedDate = day + "/" + month + "/" + year;
    
        // Check if the formatted date is in the unavailableDates array
        if (jQuery.inArray(formattedDate, unavailableDates) == -1) {
            return [true, ""];  // Date is available
        } else {
            return [false, "", "Unavailable"];  // Date is unavailable
        }
    }
})