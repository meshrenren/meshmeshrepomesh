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
			win.document.write('<h1 style="text-align:center; font-family: monospace;">' + title+ '</h1>')
			win.document.write(data)
			win.print()
			win.close()
    	},
	}
}