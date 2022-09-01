jQuery(document).ready(function ($) {
  // var tipButton = document.querySelector('.tip-button');
  var tipButton = document.querySelectorAll('.tip-button');
  // var messageEl = document.querySelectorAll('.message')
  if (tipButton) {
    for (var i = 0; i < tipButton.length; i++) {
      tipButton[i].addEventListener('click', function () {
        var user_account = $(this).data('metamask-address');
        var price = $(this).data('metamask-amount');

        // Let's imagine you want to receive an ether tip
        const yourAddress = user_account;
        const value = ethers.utils.parseEther(String(price))._hex;  // an ether has 18 decimals, here in hex.
        const desiredNetwork = '1' // '1' is the Ethereum main network ID.

        // Detect whether the current browser is ethereum-compatible,
        // and handle the case where it isn't:
        if (typeof window.ethereum === 'undefined' || typeof web3 === 'undefined') {
          const el = document.createElement('div')
          el.innerHTML = "<a href='https://chrome.google.com/webstore/detail/metamask/nkbihfbeogaeaoehlefnkodbefgpgknn?hl=en' target='_blank'>Click Here </a> to install MetaMask extention"

          Swal.fire({
            title: extradata.const_msg.ext_not_detected,
            html: el,
            icon: "warning",
          })

        }
        else {
          if (ethereum.selectedAddress == undefined) {
            Swal.close()
            Swal.fire({
              text: "Please wait while connection establish",
              didOpen: () => {
                Swal.showLoading()
              },

              allowOutsideClick: false,
            })

            const accounts = ethereum.request({ method: 'eth_requestAccounts' }).then(function (accounts) {
              Swal.close()
            })

          }
          // In the case they approve the log-in request, you'll receive their accounts:
          else {
            // You also should verify the user is on the correct network:
            if (ethereum.networkVersion !== desiredNetwork) {
              try {
                Swal.fire({
                  // title: extradata.const_msg.required_network,
                  text: "This application requires the main network,Click ok to swtich the network",
                  icon: "warning",
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  confirmButtonText: 'Ok',
                  reverseButtons: true,

                }).then((result) => {
                  if (result.isConfirmed) {
                    const chain_change = ethereum.request({
                      method: 'wallet_switchEthereumChain',
                      params: [{ chainId: '0x1' }],
                    }).then(function (accounts) {
                      location.reload();
                    })

                  }
                })


              } catch (switchError) {

              }

            }
            else {
              Swal.fire({
                title: "Confirm amount in Ethereum",
                //  html: confirm_payment ,
                // icon: "warning",         
                allowOutsideClick: false,
                html: `<input type="text" value="${price}" class="swal2-input" id="donation_amount" placeholder="Enter amount">`,
                preConfirm: () => {
                  const donation_amount = Swal.getPopup().querySelector('#donation_amount').value
                  if (!donation_amount) {
                    Swal.showValidationMessage(`Please enter amount`)
                  }

                  return { donation_amount: donation_amount }
                },
                showCancelButton: true,
                confirmButtonText: 'Confirm',
                reverseButtons: true,
              }).then((result) => {
                //  console.log(result)
                if (result.isConfirmed) {

                  ethereum.request({ method: 'eth_requestAccounts' }).catch(function (reason) {

                  }).then(function (accounts) {   // In the case they approve the log-in request, you'll receive their accounts:                
                    const account = accounts[0]
                    sendEtherFrom(account, result.value, function (err, transaction) {
                      if (err) {
                        return;
                      }




                    })

                  })
                }
              })

            }

          }
        }

        function sendEtherFrom(account, price, callback) {
          Swal.fire({
            title: "Confirm transaction from wallet",
            icon: 'question',
            didOpen: () => {
              Swal.showLoading()
            },
            // imageUrl: extradata.url + "/assets/images/metamask.png",
            allowOutsideClick: false,
          })
          const method = 'eth_sendTransaction'
          const parameters = [{
            from: account,
            to: yourAddress,
            value: ethers.utils.parseEther(price.donation_amount)._hex,
            gas: '0xa028',
          }]
          const from = account

          // Now putting it all together into an RPC request:
          const payload = {
            method: method,
            params: parameters,
            from: from,
          }

          try {
            const provider = new ethers.providers.Web3Provider(window.ethereum, "any");
            const signer = provider.getSigner()
            var secret_code = "";
            const tx = {
              from: from,
              to: yourAddress,
              value: ethers.utils.parseEther(price.donation_amount)._hex,
              gasLimit: ethers.utils.hexlify("0x5208"), // 21000

            }

            const trans = signer.sendTransaction(tx).then(async function (res) {
              Swal.close()

              Swal.fire({
                title: "Transaction in Process ! Please Wait ",
                //   imageUrl: extradata.url + "/assets/images/metamask.png",
                //   footer: process_messsage,
                didOpen: () => {
                  Swal.showLoading()
                },
                allowOutsideClick: false,
              })

              return res.wait();
            }).then(function (tx) {
              Swal.close()
              Swal.fire({
                title: "Trasaction completed successfully !",
                icon: 'success',
                timer: 2000,
              })
            }).catch(function (error) {
              if (error.code == "4001") {
                Swal.close()
                Swal.fire({
                  title: "Trasaction rejected",
                  icon: 'error',
                  timer: 2000,
                })

                return;
              }
            });
          }
          catch (erro) {
            console.log(erro)
          }


        }

      });
    }
  }
});
