<?php defined( 'ABSPATH' ) || die( 'Cheatin’ uh?' ); ?>
<div class="wrio-swal-subtitle"><?php _e( 'What you need to know before starting conversion.', 'robin-image-optimizer' ); ?></div>
<div class="wrio-swal-content">
    <ul class="wrio-list-infos">
        <li>
            <span class="wrio-info-icon">
                <svg width="36" height="33" viewBox="0 0 36 33" xmlns="http://www.w3.org/2000/svg">
                    <g fill="#ccc" fill-rule="nonzero">
                        <path d="m35.38 26.295c0-.002 0-.002 0 0l-13.501-23.787c-.8-1.41-2.247-2.252-3.873-2.252-1.625 0-3.071.843-3.872 2.252l-13.5 23.786c-.791 1.393-.78 3.051.027 4.437.807 1.386 2.246 2.214 3.846 2.214l27 0c1.6 0 3.038-.828 3.844-2.212.807-1.386.818-3.045.029-4.437m-1.325 3.681c-.534.919-1.488 1.469-2.548 1.469l-27 0c-1.062 0-2.01-.549-2.55-1.469-.534-.919-.541-2.02-.018-2.941l13.5-23.787c.529-.934 1.489-1.493 2.566-1.493 1.078 0 2.038.558 2.568 1.493l13.502 23.786c.523.924.516 2.025-.02 2.943"></path>
                        <path d="m18.01 9.459c-.414 0-.75.336-.75.75l0 10.772c0 .414.336.75.75.75.414 0 .75-.336.75-.75l0-10.772c0-.414-.336-.75-.75-.75"></path>
                        <path d="m18.01 24.933c-.414 0-.75.336-.75.75l0 .373c0 .414.336.75.75.75.414 0 .75-.336.75-.75l0-.373c0-.416-.336-.75-.75-.75"></path>
                    </g>
                </svg>
            </span>
            <span><?php _e( 'If you choose to convert now, you should keep this page open until a bulk conversion is performed. If you leave, the conversion process will stop and you will need to run it again..', 'robin-image-optimizer' ); ?></span>
        </li>
        <li>
            <span class="wrio-info-icon">
                <svg width="36" height="36" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                    <g fill="#ccc" fill-rule="nonzero">
                        <path d="m18 36c-9.925 0-18-8.07-18-18 0-9.925 8.07-18 18-18 9.925 0 18 8.07 18 18 0 9.925-8.07 18-18 18m0-34.435c-9.06 0-16.435 7.372-16.435 16.435 0 9.06 7.372 16.435 16.435 16.435 9.06 0 16.435-7.372 16.435-16.435 0-9.06-7.372-16.435-16.435-16.435"></path>
                        <path d="m27.391 18.783l-9.391 0c-.432 0-.783-.351-.783-.783l0-12.522c0-.432.351-.783.783-.783.432 0 .783.351.783.783l0 11.739 8.609 0c.432 0 .783.351.783.783 0 .432-.351.783-.783.783"></path>
                    </g>
                </svg>
            </span>
            <span><?php
				$optimize_type = WRIO_Plugin::app()->getOption( 'image_optimization_type', 'schedule' );
				if ( $optimize_type === 'schedule' ) {
					_e( 'If you choose to a scheduled conversion, you can leave this page or close the browser. A plugin without your participation will convert images via cron until it convert them all. This conversion method is slower than its alternative, but you don’t have to waste your time on manual conversion.', 'robin-image-optimizer' );
				} else if ( $optimize_type === 'background' ) {
					_e( 'If you have selected background conversion, you can leave this page or close the browser. The plugin will convert the images without your participation until it convert them all. This conversion method is faster than scheduled conversion and manual conversion. (beta version).', 'robin-image-optimizer' );
				}
				?></span>
        </li>
        <li>
            <span class="wrio-info-icon">
                <svg width="36" height="47" viewBox="0 0 36 47" xmlns="http://www.w3.org/2000/svg">
                    <g fill="#ccc" fill-rule="nonzero" transform="translate(-594-569)">
                        <path d="m13.304 31.3l-7.826 0c-.432 0-.783.351-.783.783 0 .432.351.783.783.783l7.826 0c.432 0 .783-.351.783-.783 0-.432-.351-.783-.783-.783m0-4.696l-7.826 0c-.432 0-.783.351-.783.783 0 .432.351.783.783.783l7.826 0c.432 0 .783-.351.783-.783 0-.432-.351-.783-.783-.783m0 9.391l-7.826 0c-.432 0-.783.351-.783.783 0 .432.351.783.783.783l7.826 0c.432 0 .783-.351.783-.783 0-.432-.351-.783-.783-.783m0 4.696l-7.826 0c-.432 0-.783.351-.783.783 0 .432.351.783.783.783l7.826 0c.432 0 .783-.351.783-.783 0-.432-.351-.783-.783-.783m-8.609-22.696c0 .432.351.783.783.783l25.04 0c.432 0 .783-.351.783-.783 0-.432-.351-.783-.783-.783l-25.04 0c-.432 0-.783.351-.783.783m30.522-18l-21.913 0c-.105 0-.207.022-.302.061-.045.019-.08.053-.121.08-.044.03-.094.05-.131.088l-12.522 12.522c-.036.036-.056.085-.085.127-.028.042-.064.078-.083.125-.039.095-.061.197-.061.302l0 32.87c0 .432.351.783.783.783l34.435 0c.432 0 .783-.351.783-.783l0-45.39c0-.432-.351-.783-.783-.783m-22.696 2.672l0 9.85-9.85 0 9.85-9.85m21.913 42.719l-32.87 0 0-31.3 11.739 0c.432 0 .783-.351.783-.783l0-11.739 20.348 0 0 43.826m-3.913-23.478l-25.04 0c-.432 0-.783.351-.783.783 0 .432.351.783.783.783l25.04 0c.432 0 .783-.351.783-.783 0-.432-.351-.783-.783-.783m0 4.696l-14.09 0c-.432 0-.783.351-.783.783l0 14.09c0 .011.006.019.006.03.003.063.019.121.036.182.011.039.017.08.034.116.022.047.056.086.086.128.03.041.056.083.094.117.009.009.014.02.023.03.03.025.066.033.097.052.045.028.091.058.142.077.05.017.1.023.152.031.038.003.072.02.111.02l14.09 0c.161 0 .302-.059.426-.144.014-.009.03-.005.044-.014.014-.011.02-.025.033-.036.044-.038.08-.081.114-.128.028-.038.055-.072.075-.111.022-.044.034-.091.049-.141.014-.052.028-.102.031-.155.002-.019.011-.033.011-.052l0-14.09c0-.434-.351-.784-.783-.784m-12.417 14.09l2.446-2.936 2.576 1.288c.34.171.75.072.977-.23l1.722-2.295 3.13 4.173-10.852 0m11.634-1.567l-3.287-4.383c-.296-.394-.958-.394-1.252 0l-1.957 2.608-2.547-1.273c-.327-.164-.72-.08-.952.199l-2.528 3.033 0-11.14 12.522 0 0 10.955m-9.391-6.259c.862 0 1.565-.703 1.565-1.565 0-.862-.703-1.565-1.565-1.565-.862 0-1.565.703-1.565 1.565 0 .862.703 1.565 1.565 1.565"
                              transform="translate(594 569)" fill="#ccc" fill-rule="nonzero"></path>
                    </g>
                </svg>
            </span>
            <span>
            <?php _e( 'Remember that the conversion of a large number of images may take some time depending on your server and network speed.', 'robin-image-optimizer' ); ?>
            <span>
            </span></span>
        </li>
    </ul>
</div>
<!-- wrio-swal-content -->