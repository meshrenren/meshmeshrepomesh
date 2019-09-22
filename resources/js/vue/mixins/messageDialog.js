window.noty = require('noty')
import Noty from 'noty'

export default {
      methods: {
            showMessage(type = 'error', message, time = 2500) {
                  new Noty({
                        theme: 'relax',
                        type: type,
                        layout: 'topRight',
                        text: message,
                        timeout: time
                  }).show();
            },

      }
}