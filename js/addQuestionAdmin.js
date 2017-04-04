/**
 * Created by adria on 4/04/17.
 */
function checkQuestions(){
    var bool = true;
    if($('#enunciado').val().length == 0){ bool = false }
    if($('#respuesta1').val().length == 0){ bool = false }
    if($('#respuesta2').val().length == 0){ bool = false }
    if($('#respuesta3').val().length == 0){ bool = false }
    if($('#respuesta4').val().length == 0){ bool = false }
    return bool;


}
function recordQuestion(){
    $.ajax({
        type: 'POST',
        data:{
            funcion: 'insertQuestion',
            Id: 'default',
            Statement: $('#enunciado').val(),
            Answer1: $('#respuesta1').val(),
            Answer2: $('#respuesta2').val(),
            Answer3: $('#respuesta3').val(),
            Answer4: $('#respuesta4').val(),
            CorrectAnswer:$('#respuestaCorrecta').val(),
            IdTheme:$('#tema').val()
        },
        url: '../php/questionsAdmin.php',
        success: function(data){

            if(data){
                $('.questions').append('<tr>' +
                    '<td>' + $('#tema option:selected').html() + '</td>' +
                    '<td>' + $('#enunciado').val() + '</td>' +
                    '<td> <button class="btn btn-default" type="button" name="refresh" aria-label="refresh" title="Refresh">' +
                    '<i class="glyphicon glyphicon-trash icon-trash"></i>'+
                    '</button>' +
                    '<button class="btn btn-default icons" type="button" name="refresh" aria-label="refresh" title="Refresh">' +
                    '<i class="glyphicon glyphicon-pencil icon-pencil "></i>'+
                    '</button></td>');
                closeDialog("#questionForm");
                cleanInputsOfDialog();
            }
        }
    })
}
function clearForm(){
    $('#enunciado').val('');
    $('#respuesta1').val('');
    $('#respuesta2').val('');
    $('#respuesta3').val('');
    $('#respuesta4').val('');
}