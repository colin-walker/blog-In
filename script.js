
function toggleForm() {
    var editdiv = document.getElementById('editdiv');
    var editicons = document.getElementsByClassName('editicon');
    var delicons = document.getElementsByClassName('delicon');

        if (editdiv.style.height == "0px" && editicons[0].style.display == 'block') {
            for (var i = 0; i < editicons.length; i++) {
                editicons[i].style.display = 'none';
            }
            for (var i = 0; i < delicons.length; i++) {
        		delicons[i].style.display = 'none';
            }

            document.getElementById('toggle').style.display = 'block';
            document.getElementById('cancel').style.display = 'none';
        } else
        if (editdiv.style.height == "0px" && editicons[0].style.display != 'block') {
        	for (var i = 0; i < delicons.length; i++) {
                delicons[i].style.display = 'block';
            }
            for (var i = 0; i < editicons.length; i++) {
                editicons[i].style.display = 'block';
            }
            
            var trueHeight = editdiv.scrollHeight + 10;
            editdiv.style.height = trueHeight + "px";
            editdiv.style.marginBottom = "50px";
            document.getElementById('toggle').style.display = 'none';
            document.getElementById('cancel').style.display = 'block';
            var contentArea = document.getElementById('content');
            contentArea.setSelectionRange(0, 0);
            contentArea.focus();
            var main = document.getElementById('main');
            main.scrollIntoView();
            
            rect = main.getBoundingClientRect();
    	    recttop = rect.top;
    	    console.log(recttop);
    	    window.scrollTo(0, recttop+100);
            
        } else {
            editdiv.style.height = "0px";
            editdiv.style.marginBottom = "0px";
            for (var i = 0; i < editicons.length; i++) {
                editicons[i].style.display = 'none';
            }
            for (var i = 0; i < delicons.length; i++) {
        		delicons[i].style.display = 'none';
        	}

            document.getElementById('toggle').style.display = 'block';
            document.getElementById('cancel').style.display = 'none';
        }

        if (editdiv.style.height == "0px" && document.getElementById('toggle').style.display == 'none') {
            document.getElementById('toggle').style.display = 'block';
            document.getElementById('cancel').style.display = 'none';
            for (var i = 0; i < editicons.length; i++) {
                editicons[i].style.display = 'none';
            }
            for (var i = 0; i < delicons.length; i++) {
        		delicons[i].style.display = 'none';
            }
        }
    }
    
    
    
function toggleLetterForm() {
    var editdiv = document.getElementById('editdiv');

    if (editdiv.style.height == "0px") {
    	var trueHeight = editdiv.scrollHeight + 10;
        editdiv.style.height = trueHeight + "px";
        editdiv.style.marginBottom = "50px";
        document.getElementById('toggle').style.display = 'none';
        document.getElementById('cancel').style.display = 'block';
        var title = document.getElementById('title');
        title.setSelectionRange(0, 0);
        title.focus();
        var main = document.getElementById('main');
        main.scrollIntoView();
    } else {
        editdiv.style.height = "0px";
        editdiv.style.marginBottom = "0px";
    	document.getElementById('toggle').style.display = 'block';
        document.getElementById('cancel').style.display = 'none';
    }
}    



	function hideForm() {
		document.getElementById('editdiv').style.height = "0px";
		document.getElementById('editdiv').style.marginBottom = "0px";
	}



    function toggleEdit(e) {
        let post = "post" + e;
        let edit = "edit" + e;
        let area = "text" + e;
        let newcontent = "newcontent" + e;

        var delicons = document.getElementsByClassName('delicon');
        for (var i = 0; i < delicons.length; i++) {
        	delicons[i].style.display = 'none';
        }

        var editdivs = document.getElementsByClassName('editdivs');
        for (var i = 0; i < editdivs.length; i++) {
            editdivs[i].style.display = 'none';
        }

        var editdiv = document.getElementById('editdiv');
        editdiv.style.height = '0px';
        editdiv.style.marginBottom = "0px";
	    document.getElementById(post).style.display='none';
	    document.getElementById(edit).style.display='block'
	    var contentArea = document.getElementById(newcontent);
	    // console.log(contentArea[0].value);
	    var areaLen = contentArea.value.length;
	    contentArea.setSelectionRange(areaLen, areaLen);
        contentArea.focus();
        contentArea.scrollTop = contentArea.scrollHeight;
    }



    function quit(e) {
        let post = "post" + e;
        let edit = "edit" + e;
	    document.getElementById(post).style.display='block';
        document.getElementById(edit).style.display='none';
        document.getElementById('editdiv').style.height = '0px';
        
    }



    function toggleImage() {
        var editdiv = document.getElementById('editdiv');
        var frame = document.getElementById('upload_frame');
        if (frame.style.display == 'block') {
            frame.style.display = 'none';
            var trueHeight = 291;
        } else {
            frame.style.display = 'block';
            var trueHeight = editdiv.scrollHeight;
        }
        editdiv.style.height = trueHeight + "px";
    }



    function toggleImage_edit(e) {
        var edit_upload_frame = "edit_upload_frame" + e;
        var frame = document.getElementById(edit_upload_frame);
        if (frame.style.display == 'block') {
            frame.style.display = 'none';
        } else {
            frame.style.display = 'block';
        }
    }



    function toggleComments(ID) {
        var repliesdiv = 'replies' + ID;
        var replies = document.getElementById(repliesdiv);
        replies.style.transition = "all .5s";
        var trueHeight = replies.scrollHeight + 10;
        if (replies.style.height != "0px") {
            replies.style.height = "0px";
            replies.style.marginTop = "0px";
            replies.style.marginBottom = "0px";
            replies.style.padding = "0px";
            var commentid = 'commenticon' + ID;
            var commenticon = document.getElementById(commentid);
            commenticon.scrollIntoView();
        } else {
            var repliesdivs = document.getElementsByClassName('replies');
            for (var i = 0; i < repliesdivs.length; i++) {
                repliesdivs[i].style.height = "0px";
                repliesdivs[i].style.marginTop = "0px";
                repliesdivs[i].style.marginBottom = "0px";
                repliesdivs[i].style.padding = "0px";
            }

            replies.style.height = trueHeight + "px";
            replies.style.marginTop = "30px";
            replies.style.marginBottom = "30px";
            replies.style.padding = "20px 15px 0px";
            var leave = 'replies' + ID;
            var name = 'name' + ID;
            var leave_reply = document.getElementById(leave);
            var namefield = document.getElementById(name);
            namefield.setSelectionRange(0, 0);
            namefield.focus();
            replies.scrollIntoView();
        }
    }



    function fragmention() {
	    var loc = location.href;
	    var locIndex = loc.indexOf("##");

	    if (locIndex !== -1) {
		    var hash = loc.substring(locIndex+2).toLowerCase();

	        while (hash.indexOf('+') != -1) {
	            hash = hash.replace("+", " ");
	        }
	    }

	    var pTags = document.getElementsByTagName("p");

	    for (var p = 0; p < pTags.length; p++) {
		    var pContent = pTags[p].innerHTML.toLowerCase();

  		    if (pContent.indexOf(hash) !== -1) {
    		    pTags[p].setAttribute("fragmention", "");
    		    pTags[p].scrollIntoView();
        		break;
  	  	    }
	    }
    }



    function backlink(source,fragmention) {
        var page = document.body.textContent;
        //console.log(page);
        var len = fragmention.length;
        var replace = '<a class="backlink" href="' + source + '">' + fragmention + '</a>';
        var fragIndex = page.indexOf(fragmention);
        //console.log(len);
        //console.log(fragIndex);

        var pTags = document.getElementsByTagName("p");
        var x, y, pContent;

        for (var p=0; p<pTags.length; p++) {
            pContent = pTags[p].innerHTML.toLowerCase();
            x = pTags[p].innerHTML;
            //console.log(x);

            if (x.indexOf(fragmention) !== -1)  {
                y = x.replace(fragmention, replace);
                console.log(y);
                pTags[p].innerHTML = y;
                break;
            }
        }
    }
