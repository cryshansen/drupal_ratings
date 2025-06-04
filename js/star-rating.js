//alert("Welcome!");

/*   functionality of the stars capturing the user input rating and displaying the textarea if less than 5*/
  function getActive(value){

	var rate = document.querySelector('.rating__input:checked').value;
	rate = formatRating(rate);
	alert("Hello! getActive"  + rate);
	//clear any previous messages and hide the element
	document.getElementById('message').innerHTML = "";
    document.getElementById('message').classList.add("d-none");

	console.log( document.getElementById('rateResponse').placeholder );
	
	//if select is 5 just hide the message box and submit else show message box and button
	if(rate == 5){
		var classLt = document.getElementById('rateFeedback').classList;
		
		//use bootstrap built in d-none css class
		if(!classLt.contains("d-none")){
		  classLt.add("d-none");
		}
		//rateMe();
		
	}else{
		var classLt = document.getElementById('rateFeedback').classList;
		       
		if(classLt.contains("d-none")){
		  classLt.remove("d-none");
		 // classLt.add("show");
		}else if(classLt.contains("hide")){
			classLt.remove("hide");
			//classLt.add("show");
		}
		// the message box to get feedback from the user.
		document.getElementById('rateResponse').placeholder = "What can make this contribution better? Your rating is " + rate;
	}
	
} 

/*   functionality of the stars capturing the user input rating and displaying the textarea if less than 5*/

	function handleRatingClick(value){
		var rate = document.querySelector('.rating__input:checked').value;
		alert("Hello! handle 5 stars!" + rate);
		
		var classLt = document.getElementById('rateFeedback').classList;
		       
		if(classLt.contains("d-none")){
		  classLt.remove("d-none");
		 // classLt.add("show");
		}
		
		
	}
	
	function formatRating(value) {
	  // Replace the underscore with a dot using a regular expression
	  return value.replace(/_/, '.');
	}
