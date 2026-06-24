<!-- Message -->
    <?php if ( $fyp_msg && isset( $messages[$fyp_msg] ) ) :
        [$mtype, $mtext] = $messages[$fyp_msg]; ?>
        <div class="fyp-notice fyp-notice-<?php echo $mtype === 'success' ? 'success' : 'error'; ?>" style="margin:16px 28px 0;">
            <?php if ($mtype==='success') : ?>
                <svg width="15" height="15" fill="none" viewBox="0 0 16 16"><circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M5 8l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <?php endif; ?>
            <?php echo esc_html( $mtext ); ?>
        </div>
    <?php endif; ?>