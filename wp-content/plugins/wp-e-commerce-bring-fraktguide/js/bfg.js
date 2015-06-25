jQuery(document).ready(function($) {
  inputField = $('#zipcode');
  outputElementCity = $("input[title='shippingcity']");
  outputElementPostCode = $("input[title='shippingpostcode']");
  currentCountry = $('#current_country');
	if (currentCountry.val() == 'NO') {
		if (inputField.val().length == 4) {
			$.getJSON('http://fraktguide.bring.no/fraktguide/postalCode.json?pnr='+ inputField.val() +'&callback=?',
			function(data){
				outputElementCity.val(data.result);
				outputElementCity.attr('readonly', true);
				outputElementPostCode.val(inputField.val());
				outputElementPostCode.attr('readonly', true);
			});
		}
		else {
			outputElementCity.val('');
			outputElementPostCode.val('');
		}
	}
});