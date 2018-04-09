Vue.modalError = {
	show: function(stackTrace) {
		var divModalError = document.createElement("div"),
			spanTitulo = document.createElement("span"),
			spanBotaoFechar = document.createElement("span"),
			topBar = document
			.createElement("div"),
			contentModalError = document.createElement("div");
		
		divModalError.setAttribute('id', 'VuemodalErro');
		for (var i in Vue.modalError.css.style)
			divModalError.style[i] = Vue.modalError.css.style[i];

		spanTitulo.appendChild(document.createTextNode('Exception:'));
		for (var i in Vue.modalError.css.topBar.title.style)
			spanTitulo.style[i] = Vue.modalError.css.topBar.title.style[i];

		spanBotaoFechar.appendChild(document.createTextNode('X'));
		for (var i in Vue.modalError.css.topBar.closeButton.style)
			spanBotaoFechar.style[i] = Vue.modalError.css.topBar.closeButton.style[i];

		spanBotaoFechar.addEventListener('click', function() {
			divModalError.parentNode.removeChild(divModalError);
		});

		for (var i in Vue.modalError.css.topBar.style)
			topBar.style[i] = Vue.modalError.css.topBar.style[i];

		topBar.appendChild(spanTitulo);
		topBar.appendChild(spanBotaoFechar);

		divModalError.appendChild(topBar);

		contentModalError.setAttribute('class', 'content');
		for (var i in Vue.modalError.css.content.style)
			contentModalError.style[i] = Vue.modalError.css.content.style[i];

		divModalError.appendChild(contentModalError);

		document.body.appendChild(divModalError);

		/*var divTitle = document.createElement("div");

		for (i in Vue.modalError.css.content.title.style)
			divTitle.style[i] = Vue.modalError.css.content.title.style[i];

		divTitle.appendChild(document.createTextNode('Server Error'));

		contentModalError.appendChild(divTitle);*/

		var lineDiv = document.createElement("div");
		lineDiv.innerHTML = stackTrace;
		
		for (var i3 in Vue.modalError.css.content.possibleErro.style)
			lineDiv.style[i3] = Vue.modalError.css.content.possibleErro.style[i3];
		
		contentModalError.appendChild(lineDiv);
	},
	css: {
		style: {
			display: 'block',
			width: '900px',
			position: 'absolute',
			top: '30%',
			left: '50%',
			marginLeft: '-450px',
			marginTop: '-125px',
			border: '1px solid black',
			backgroundColor: '#d5e9e2',
			zIndex: '99999'
		},
		topBar: {
			style: {
				width: '100%',
				fontSize: '12px',
				backgroundColor: '#4099ff'
			},
			title: {
				style: {
					margin: '0px 5px',
					width: '97%',
					position: 'relative',
					styleFloat: 'left',
					/* IE */
					cssFloat: 'left'
				}
			},
			closeButton: {
				style: {
					cursor: 'pointer',
					fontWeight: 'bold'
				}
			}
		},
		content: {
			style: {
				fontSize: '13px',
				maxHeight: '400px',
				overflowY: 'scroll'
			},
			title: {
				style: {
					padding: '5px 15px',
					color: '#297fa5'
				}
			},
			lineClass: {
				style: {
					margin: '5px 40px',
					color: '#7573ce'
				}
			},
			possibleErro: {
				style: {
					margin: '5px 40px',
					color: 'red'
				}
			}
		}
	}
};