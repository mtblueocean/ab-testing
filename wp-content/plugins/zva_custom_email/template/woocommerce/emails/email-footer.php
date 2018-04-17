<?php
/**
 * Email Footer
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-footer.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
															</div>
														</td>
													</tr>
												</table>
												<!-- End Content -->
											</td>
										</tr>
									</table>
									<!-- End Body -->
								</td>
							</tr>
                            <?php if ( $question_box || !isset( $question_box )) { ?>
                            <tr>
                                <td align="center" valign="top" style="padding: 0 48px;">
                                    <h2>Got questions? We’ve got answers</h2>
                                    <ul style="padding: 0;">
                                        <?php for ( $i = 1; $i <= 3; $i++ ) { ?>
                                        <li style="text-align: left; padding: 3px 0; font-size: 15px; color: #808080; list-style-position: inside;">
                                            <a href="<?php echo get_option( 'zva_ce_q_url_' . $i ); ?>" style="color: #00add8;"><?php echo get_option( 'zva_ce_q_title_' . $i ); ?></a>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top" style="padding: 16px 48px;">
                                    <h2>Want to check out some extra resources?</h2>
                                    <ul style="padding: 0;">
                                        <?php for ( $i = 1; $i <= 3; $i++ ) { ?>
                                        <li style="text-align: left; padding: 3px 0; font-size: 15px; color: #808080; list-style-position: inside;">
                                            <a href="<?php echo get_option( 'zva_ce_r_url_' . $i ); ?>" style="color: #00add8;"><?php echo get_option( 'zva_ce_r_title_' . $i ); ?></a>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </td>
                            </tr>
                            <?php } ?>
							<tr>
								<td align="center" valign="top">
									<!-- Footer -->
                                    <div style="background-color: #232323; height: 100%; padding: 25px;">
                                        <div style="padding: 5px 0;">
                                            <a target="_blank" href="https://facebook.com/zenvadev" class="fb-icon" style="display: inline-block; padding: 0 5px;">
                                                <img src="<?php echo WP_PLUGIN_URL; ?>/zva_custom_email/template/woocommerce/emails/icons/facebook.png" style="width: 40px;" />
                                            </a>
                                            <a target="_blank" href="https://youtube.com/c/zenva" class="youtube-icon" style="display: inline-block; padding: 0 5px;">
                                                <img src="<?php echo WP_PLUGIN_URL; ?>/zva_custom_email/template/woocommerce/emails/icons/youtube.png" style="width: 40px;" />
                                            </a>
                                            <a target="_blank" href="https://twitter.com/zenvatweets" class="twitter-icon" style="display: inline-block; padding: 0 5px;">
                                                <img src="<?php echo WP_PLUGIN_URL; ?>/zva_custom_email/template/woocommerce/emails/icons/twitter.png" style="width: 40px;" />
                                            </a>
                                        </div>
                                        <div style="color: #878a8b;">
                                            <p style="color:#878a8b; margin: 5px 0;">@<?php echo date('Y'); ?> <?php echo get_option( 'zva_ce_company_name' ); ?>. All Rights Reserved.</p>
                                            <p style="color:#878a8b; margin: 5px 0;"><?php echo get_option( 'zva_ce_address' ); ?>, <?php echo get_option( 'zva_ce_postal_code' ); ?></p>
                                            <p style="color:#878a8b; margin: 5px 0;"><?php echo get_option( 'zva_ce_country' ); ?> <?php echo get_option( 'zva_ce_business_number' ); ?></p>
                                        </div>
                                    </div>
									<!-- End Footer -->
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>
