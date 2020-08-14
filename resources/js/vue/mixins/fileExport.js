export default {
	methods: {
		exporter(type, name, data) {
			console.log("Here")
			let fileName = name+' - '+this.$df.formatDate(new Date(), 'YYYY-MM-DD')+'.'+type
			const url = window.URL.createObjectURL(new Blob([data]));
			const link = document.createElement('a');
			link.href = url;
			link.setAttribute('download', fileName);
			document.body.appendChild(link);
			link.click();
		},
    	winPrint(data, title){
    		let win = window.open("");
    		console.log('css', this.$baseUrl + 'css/mpdf.css')
    		let html = "<html><head>"
				html += "<style>"
				html += "table, th, td {"
				html += "border: 1px solid black;"
 	 			html += "border-collapse: collapse;}"
				html += "table.no-border, .no-border th, .no-border td {"
				html += "border: 0px solid black;"
 	 			html += "border-collapse: collapse;}"
				html += "</style>"
				html += `<title> ${title} </title>` 
				html += `<link rel="stylesheet" type="text/css" href="http://dilgcoop.localhost/css/mpdf.css">`
				html += "</head><body>"
				html += "<div class='print-container'>"
				html += data
				html += "</div></body>"
				html += `<footer style="bottom:0; right:0; position: fixed; font-family: monospace; font-size: 10px;">'+this.t('Rendered by Witty Manager')+'(www.wittymanager.com)</footer>`
				html += "</html>"

			//win.document.write('<h1 style="text-align:center; font-family: monospace;">' + title+ '</h1>' + data)
			win.document.write(html)
			setTimeout(() => {
				win.print()
			}, 500);
			
			/*win.close()*/
    	},
	}
}