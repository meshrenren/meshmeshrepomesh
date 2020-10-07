
import _forEach from 'lodash/forEach'
export default class AccountHelper{

	mergAllAccounts(loans = null, savings = null, shares = null, notIncLoan = null, allowZeroBal = null) {
        let allAccounts = []

        if(shares && shares.length > 0){
            _forEach(shares, rs =>{

                let arr = {}
                arr.member_id = rs.fk_memid
                arr.key = "SHARE_" + rs.particular_id
                arr.account_no = rs.accountnumber
                arr.product_id = rs.fk_share_product
                arr.product_name = rs.product.name
                arr.type = "SHARE"
                arr.balance = parseFloat(rs.balance).toFixed(2)
                arr.particular_id = rs.product.particular_id
                arr.amountToPay = null
                arr.amountToWithdraw = null


                allAccounts.push(arr)
            })
        }

        let savingsData = null
        if(savings && savings.length > 0){
            _forEach(savings, rs =>{
                savingsData = rs

                let arr = {}
                arr.member_id = rs.member_id
                arr.key = "SAVINGS_" + rs.particular_id
                arr.account_no = rs.account_no
                arr.product_id = rs.saving_product_id
                arr.product_name = rs.product.description
                arr.type = "SAVINGS"
                arr.balance = parseFloat(rs.balance).toFixed(2)
                arr.particular_id = rs.product.particular_id
                arr.amountToPay = null
                arr.amountToWithdraw = null

                allAccounts.push(arr)
            })
        }

        if(loans && loans.length > 0){
            _forEach(loans, rs =>{
                if(rs && rs.account_no){
                    if(!notIncLoan || (notIncLoan && notIncLoan.indexOf(rs.product.id) < 0)){
                        if(Number(rs.principal_balance) > 0 || (allowZeroBal && allowZeroBal.indexOf(rs.product.id) >= 0)){ 
                            let arr = {}
                            arr.member_id = rs.member_id
                            arr.key = "LOAN_" + rs.particular_id
                            arr.account_no = rs.account_no
                            arr.product_id = rs.loan_id
                            arr.product_name = rs.product.product_name
                            arr.type = "LOAN"
                            arr.balance = parseFloat(rs.principal_balance).toFixed(2)
                            arr.particular_id = rs.product.particular_id
                            arr.principal = parseFloat(rs.principal).toFixed(2)
                            arr.amountToPay = null

                            allAccounts.push(arr)
                        }
                    }
                }
            })
        }

        return allAccounts
    }

    setVoucherAccount(loanToPayList, otherToPay){
    	console.log('')
        let toPaySave = []
        let accToPaySave = []
        let totalOtherToPay = 0
        let totalOtherToWithdraw = 0

        if(loanToPayList && loanToPayList.length > 0){
            let hasLimitLoan = false
            _forEach(loanToPayList, la =>{
                let toPushAcc = false
                if(la.amountToPay && la.amountToPay > 0){
                    if(la.type == "LOAN" && parseFloat(la.amountToPay)  > parseFloat(la.balance) ){
                        hasLimitLoan = true
                    }
                    else{
                    	let arr
                        arr = {particular_id : la.particular_id, particular_name: null, amount: la.amountToPay, type : "CREDIT", account_no :  la.account_no, account_type : la.type}
                        toPaySave.push(arr)
                        toPushAcc = true

                        totalOtherToPay += parseFloat(la.amountToPay)
                    }
                    
                }

                if(la.amountToWithdraw && la.amountToWithdraw > 0){
                    let arr
                    arr = {particular_id : la.particular_id, particular_name: null, amount: la.amountToWithdraw, type : "DEBIT", account_no :  la.account_no, account_type : la.type}
                    toPaySave.push(arr)
                    toPushAcc = true

                    totalOtherToWithdraw += parseFloat(la.amountToWithdraw)
                }

                if(toPushAcc){
                    accToPaySave.push(la)
                }
            })

            if(hasLimitLoan){
                return {
		        	success : false, 
		            error : 'ERR_LOAN_BALANCE'
		        }
            }
        }

        if(otherToPay && otherToPay.length > 0){
            _forEach(otherToPay, la =>{
                if(la.particular_id && la.amountToPay && la.amountToPay > 0){
                	let arr
                    arr = {particular_id : la.particular_id, particular_name: null, amount: la.amountToPay, type : "CREDIT", account_type : "OTHERS"}
                    toPaySave.push(arr)
                    totalOtherToPay += parseFloat(la.amountToPay)
                    
                }
            })
        }

        let data = {toPaySave : toPaySave, 
        	accToPaySave : accToPaySave,
        	totalOtherToPay : totalOtherToPay,
            totalOtherToWithdraw : totalOtherToWithdraw}

        return {
        	success : true, 
            data : data
        }
    }
}