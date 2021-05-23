$(document).ready(function(){
var nowDate = new Date();
var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
 $('.datepicker1 ').datepicker({
            uiLibrary: 'bootstrap',
			format: 'yyyy-mm-dd',
			startDate: nowDate 
        });
		 $('.datepicker2 ').datepicker({
            uiLibrary: 'bootstrap',
			format: 'yyyy-mm-dd',
			
        });
});
$('form').on('beforeSubmit', function (e) {
    $(".loading").show();
    $('.btn-hide').hide();
	return true;
});
function changeavailabilty(tablename,tableid){
			 $.ajax({
				 type: 'post',
				 url: 'changeproductavailabilty',
				 data: {
				 tablename:tablename,
				 tableid:tableid
				 },		
				 success: function (response) {
					/* silence is golden */ 
				 }	 
				 });
}

function changestatus(tablename,tableid){
			 $.ajax({
				 type: 'post',
				 url: 'changeproductstatus',
				 data: {
				 tablename:tablename,
				 tableid:tableid
				 },		
				 success: function (response) {
					/* silence is golden */ 
				 }	 
				 });
}
function changeloginaccess(tablename,tableid){
	$.ajax({
				 type: 'post',
				 url: 'changeloginaccess',
				 data: {
				 tablename:tablename,
				 tableid:tableid
				 },		
				 success: function (response) {
					/* silence is golden */ 
				 }	 
				 });
}
function editcategory(id){
var request = $.ajax({
  url: "editcategorypopup",
  type: "POST",
  data: {id : id},
}).done(function(msg) {
	$('#foodcategerybody').html(msg);
	$('#updatefoodcategery').modal('show');
});
}
function updatepilot(id){
	
var request = $.ajax({
  url: "updatepilotpopup",
  type: "POST",
  data: {id : id},
}).done(function(msg) {
	$('#pilotbody').html(msg);
	$('#updatepilot').modal('show');
});
}
function editcoupon(id){
	
var request = $.ajax({
  url: "editcouponpopup",
  type: "POST",
  data: {id : id},
}).done(function(msg) {
	$('#editcouponbody').html(msg);
	$('#editcoupon').modal('show');
});
}

function qrcode(id)
{
	alert(id);
}
function editproductpopup(id){
var request = $.ajax({
  url: "editproductpopup",
  type: "POST",
  data: {id : id},
}).done(function(msg) {

	$('#editproductbody').html(msg);
	$('#editproduct').modal('show');
});	
}
function editroompopup(id){
var request = $.ajax({
  url: "editroompopup",
  type: "POST",
  data: {id : id},
}).done(function(msg) {

	$('#editroombody').html(msg);
	$('#editroom').modal('show');
});	
}
function edittablepopup(id)
{
var request = $.ajax({
  url: "edittablepopup",
  type: "POST",
  data: {id : id},
}).done(function(msg) {
	$('#edittablebody').html(msg);
	$('#edittable').modal('show');
});		
}
function editsectionpopup(id)
{
var request = $.ajax({
  url: "editsectionpopup",
  type: "POST",
  data: {id : id},
}).done(function(msg) {
	$('#editsectionbody').html(msg);
	$('#editsection').modal('show');
});		
}
function billview(id){
  if(id == 0 || id == '' ){
    swal(
      'Warning!',
      'Please Placer Order !!',
      'warning'
    );
    return false;
  }

        var form=document.createElement('form');
        form.setAttribute('method','post');
        form.setAttribute('action','tablebill');
        form.setAttribute('target','_blank');

    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("name", "id");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("value", id);
    form.appendChild(hiddenField);

    document.body.appendChild(form);
    form.submit();    


}
function billviewkot(id){
if(id == 0 || id == '' ){
  swal(
    'Warning!',
    'Please Placer Order !!',
    'warning'
  );
  return false;
}

        var form=document.createElement('form');
        form.setAttribute('method','post');
        form.setAttribute('action','tablekot');
        form.setAttribute('target','_blank');

    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("name", "id");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("value", id);
    form.appendChild(hiddenField);

    document.body.appendChild(form);
    form.submit();    


}

function updateingredient(id)
{
var request = $.ajax({
  url: "updateingredientpopup",
  type: "POST",
  data: {id : id},
}).done(function(msg) {
	$('#ingredientbody').html(msg);
	$('#updateingredient').modal('show');
});		
}

function updateemployee(id){
var request = $.ajax({
  url: "editemployeepopup",
  type: "POST",
  data: {id : id},
}).done(function(msg) {
	$('#employeebody').html(msg);
	$('#updateemployee').modal('show');
});
}

function updatefc(id){
var request = $.ajax({
  url: "editfcpopup",
  type: "POST",
  data: {id : id},
}).done(function(msg) {
	$('#editfcbody').html(msg);
	$('#editfc').modal('show');
});
}

function updaterole(id){
var request = $.ajax({
  url: "editrolepopup",
  type: "POST",
  data: {id : id},
}).done(function(msg) {
	$('#rolebody').html(msg);
	$('#updaterole').modal('show');
});
}

function placeOrder(id,name,current_order_id)
{
	        var form=document.createElement('form');
        form.setAttribute('method','post');
        form.setAttribute('action','placeorder');
        //form.setAttribute('target','_blank');

    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("name", "tableid");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("value", id);
    form.appendChild(hiddenField);

    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("name", "tableName");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("value", name);
    form.appendChild(hiddenField);

    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("name", "current_order_id");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("value", current_order_id);
    form.appendChild(hiddenField);

    document.body.appendChild(form);
    form.submit();   
}