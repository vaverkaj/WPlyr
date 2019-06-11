function add_editor_field() {
    var node = document.getElementById("wplyr_hidden_path_editor");
    node = node.cloneNode(true);
    node.id = "";
    node.hidden = false;
    document.getElementById("wplyr_editor_container").appendChild(node);
    init_php_file_tree();
}

function remove_editor_field(parent) {
    parent.parentNode.removeChild(parent);
}

function openTab(element, type) {
    while (!hasClass(element,'wplyr_editor_border')) {
        element = element.parentNode;
    }
    var i;
    var x = element.getElementsByClassName("wplyr-tab");
    for (i = 0; i < x.length; i++) {
        if (type === 'Video' && hasClass(x[i],'wplyr-video-tab') || type === 'YouTube' && hasClass(x[i],'wplyr-youtube-tab')) {
            x[i].style.display = "block";
            setType(element, type.toLowerCase());
        } else {
            x[i].style.display = "none";
        }
    }
    x = element.getElementsByClassName("wp_wplyr_video_source_value");
    for (i = 0; i < x.length; i++) {
		if(x[i].tagName == 'B'){
			x[i].textContent = "";
		}else if(x[i].tagName == 'INPUT'){
			x[i].value = "";
		}
	}
}

function setType(inputParent, type){
    inputs = inputParent.getElementsByClassName("wp_wplyr_video_type_value");
    for(i = 0; i < inputs.length; i++){
        inputs[i].value = type;
    }
}

function updateInputs(){
    element = event.target;
    while (!hasClass(element,'wplyr_editor_border')) {
        element = element.parentNode;
    }
    inputs = element.getElementsByClassName("wp_wplyr_video_source_value");
    for(i = 0; i < inputs.length; i++){
        inputs[i].value = event.target.value;
    }
}

//document.addEventListener('DOMContentLoaded', () => {add_editor_field()});