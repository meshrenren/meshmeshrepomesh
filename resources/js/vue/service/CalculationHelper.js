
import DateFormatter from './../service/DateFormatter'

var $systemDate = window.systemDate // Is from the current date set in the beginning of the day
var $df = new DateFormatter()
export default class CalculationHelper{

	checkVersion(date){
		let vr = '1' // For old calculation

		let release_date = $df.formatDate(date, "X")

    	let new_policy = $df.formatDate("2020-08-10", "X")
		if(release_date >= new_policy){
			vr = '1-2020.08' //New policy update from August 2020. Check documentatoin of the new policy
		}

		return vr
	}
	/*
	Get date from Open Accunt to system(current) date
	*/
	tdDates(openDate){
		
	}
	/*
	Get interest for prepaid policy loan.
	Most loan policy id prepaid
	* @param amount Number - Amount paid
	* @param interest Number - Interes rate

	*/
	getInterest(amount, interest){
		let amtInterest =  parseFloat(amount) * parseFloat(interest)

		return amtInterest
	}
   
}