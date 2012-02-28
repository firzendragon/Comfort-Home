var tbid1 = "#tbcontainer1";
var tbid2 = "#tbcontainer2";
var inputfomate = "<input type='text'/>";
var pre = null;
var tdinputselection="";

function getSelectionStart(o) {
    if (o.createTextRange) {
        var r = document.selection.createRange().duplicate();
        r.moveEnd('character', o.value.length);
        if (r.text == '') return o.value.length
        return o.value.lastIndexOf(r.text);
    } else return o.selectionStart;
}
function getSelectedText() {
    if (window.getSelection) {
        return window.getSelection().toString();
    }
    else if (document.getSelection) {
        return document.getSelection();
    }
    else if (document.selection) {
        return document.selection.createRange().text;
    }
}
$(function() {
    $(tbid1).click(function(e) {
        stopevent(e);
        var cur = e.target;
        if ($(cur).is("td")) {
            switchtdinput(cur);
        }
    });
    $(tbid1).find("tr").each(function() {
        $(this).find("td").each(function(i) {
            $(this).data("i", i);
        });
    });
    $(tbid2).click(function(e) {
        stopevent(e);
        var cur = e.target;
        if ($(cur).is("td")) {
            switchtdinput(cur);
        }
    });
    $(tbid2).find("tr").each(function() {
        $(this).find("td").each(function(i) {
            $(this).data("i", i);
        });
    });
});

function switchtdinput(obj) {
    if (pre) doinpleave(pre);
    var width = $(obj).width() * 1.1;
    var objtxt=$.trim($(obj).text());
    $(obj).html($(inputfomate).val(objtxt));
    pre = $(obj).find('input').css("border", "1px solid red").width(width)[0];
    $(obj).find('input').trigger("focus").trigger("select");
    $(obj).find('input').click(function(event) {
        stopevent(event);
        return false;
    }).blur(function(event) {
        inpleave(event, this);
    }).keypress(function(event) {
        stopevent(event);
    }).keyup(function(event) {
        return setpreornext(event, this);
    }).keydown(function(event) {
	    tdinputselection=getSelectedText();
        stopevent(event);
    });
}

function stopevent(event) {
    event.stopPropagation();
}

function inpleave(event, obj) {
    stopevent(event);
    doinpleave(obj);
}

function doinpleave(obj) {
    if (!obj) return;
    var val = $(obj).val();
    var $par = $(obj).parent()[0];
    $($par).text(val);
    pre = null;
}

function setpreornext(event, objThis) {
    var count = -1;
    stopevent(event);
    var obj;
    if (event.keyCode == 40 || event.which == 40 || event.keyCode == 38 || event.which == 38 || event.keyCode == 13 || event.which == 13|| event.keyCode == 37 || event.which == 37|| event.keyCode == 39 || event.which == 39) {
       count = parseInt($(objThis).parent().data("i"));
        if (event.keyCode == 40 || event.which == 40) {
            obj = $(objThis).parent().parent().next("tr").find("td").get(count);
        } else if (event.keyCode == 38 || event.which == 38) {
            obj = $(objThis).parent().parent().prev("tr").find("td").get(count);
        } else if (event.keyCode == 13 || event.which == 13) {
            obj = $(objThis).parent().parent().find("td").get(count + 1);
        }
        else if (event.keyCode == 37 || event.which == 37) {          
            if(getSelectionStart(objThis)==0&&(tdinputselection!=$(objThis).val())){       
                obj = $(objThis).parent().parent().find("td").get(count - 1);
            }
        }
        else if (event.keyCode == 39 || event.which == 39) {
            if(getSelectionStart(objThis)==$(objThis).val().length&&(tdinputselection!=$(objThis).val()))
            obj = $(objThis).parent().parent().find("td").get(count + 1);
        }
        if (obj) {
            doinpleave(objThis);
            switchtdinput(obj);
        }
    }
    if (event.keyCode == 13 || event.which == 13) return false;
    else return true;

}