export default class DateFormatter{

    formatDate(date, format){
      	if(date){
        	return moment(date).format(format)
      	}
      	return null
    }
}