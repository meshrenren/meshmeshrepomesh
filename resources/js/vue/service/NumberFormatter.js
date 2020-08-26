export default class NumberFormatter{
	
	percentToDecimal(percent){
		return Number(percent) / 100
	}

	numberFixed(number, count){
		let num = Number(number).toFixed(count)
		return num
	}

	formatNumber(number, fixedCount = null) {
		/*if(number){
            number = parseFloat(number)
            if((number % 1) == 0 ){
                number = number.toFixed(2)
            }
	  		return number.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }
        else if(returnZer0){
            return '0.00'
        }

		return '';*/
		if(number == null || number == ""){
			return ""
		}


		number = Number(number)
		if(fixedCount){
			number = Number(number).toFixed(fixedCount)
		}

		let splitNum = number.toString().split(".");
		let num = splitNum[0]
		num = num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')

		if(splitNum.length > 1){
			num = num+"."+splitNum[1]
		}
	  	return num
	}
}