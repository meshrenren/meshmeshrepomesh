    
window.noty = require('noty')
import Noty from 'noty'
import _forEach from 'lodash/forEach'

export default {
    methods: {
        mergAllAccounts(loans = null, savings = null, shares = null) {
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

                    allAccounts.push(arr)
                })
            }

            if(loans && loans.length > 0){
                _forEach(loans, rs =>{
                    if(Number(rs.principal_balance) > 0){ 
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
                })
            }

            return allAccounts
        },

        setVoucherAccount(loanToPayList, otherToPay){
            let toPaySave = []
            let accToPaySave = []
            let totalOtherToPay = 0

            if(loanToPayList && loanToPayList.length > 0){
                let hasLimitLoan = false
                _forEach(loanToPayList, la =>{
                    if(la.amountToPay && la.amountToPay > 0){
                        if(parseFloat(la.amountToPay)  > parseFloat(la.balance) ){
                            hasLimitLoan = true
                        }
                        else{
                            arr = {particular_name: getPi.name, amount: la.amountToPay, type : "CREDIT", account_no :  la.account_no, account_type : la.type}
                            toPaySave.push(la)
                            accToPaySave.push(arr)

                            totalOtherToPay += parseFloat(la.amountToPay)
                        }
                        
                    }
                })

                if(hasLimitLoan){
                    new Noty({
                        type: 'error',
                        layout: 'topRight',
                        text: 'AMOUNT TO PAY is greater than LOAN BALANCE',
                        timeout: 5000
                    }).show()
                    return false
                }
            }

            if(otherToPay && otherToPay.length > 0){
                _forEach(otherToPay, la =>{
                    if(la.particular_id && la.amountToPay && la.amountToPay > 0){
                        arr = {particular_id : la.particular_id, particular_name: null, amount: la.amountToPay, type : "CREDIT", account_type : "OTHERS"}
                        toPaySave.push(arr)
                        totalOtherToPay += parseFloat(la.amountToPay)
                        
                    }
                })
            }
            return {
                toPaySave : toPaySave,
                totalOtherToPay : totalOtherToPay
            }
        }

    }
}