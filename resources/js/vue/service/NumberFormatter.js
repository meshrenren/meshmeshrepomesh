export default class NumberFormatter{
	
	percentToDecimal(percent){
		return Number(percent) / 100
	}

	numberFixed(number, count){
		let num = Number(number).toFixed(count)
		return num
	}
}