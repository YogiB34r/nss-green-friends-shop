<?php
add_filter( 'wc_empty_cart_message', 'custom_wc_empty_cart_message' );
function custom_wc_empty_cart_message() {
    $custum_html = '<div id="core" class="borderedWrapper">
                            <form name="allfrm" method="post" action="//www.nonstopshop.rs/cms/identification.php">
                                                                    
                                    <p class="titleSmall">Proizvodi u korpi:</p>
    
                                    <p class="cartText3"><strong>U Vašoj korpi trenutno nema proizvoda.</strong></p>
                                    
                                    <p class="cartText3">Da biste naručili proizvod(e) potrebno je da ih prethodno dodate u korpu. <br>
                                        Proizvod se dodaje u korpu klikom na dugme "Stavi u korpu" koje se nalazi na stranici 
                                        svakog proizvoda.</p><br>
                                    
                                    <img src="/wp-content/uploads/2018/07/btn_add_to_cart.png" alt="dodaj u korpu">
                                                                    
                                    <p class="cartText3 intro-text"><strong>napomena:</strong><br>
                                        Pre nego što započnete sa naručivanjem potrebno je da se "registrujete". Link za registraciju se 
                                        nalazi na vrhu svake stranice. Registracija se obavlja samo jednom nakon čega će vas sistem 
                                        automatski prepoznati svaki sledeći put kada posetite sajt.</p>
                            </form> 
                            </div>';
    echo $custum_html;
}