<!-- Message -->
    <?php if ( $gig_msg && isset( $messages[$gig_msg] ) ) :
        [$mtype, $mtext] = $messages[$gig_msg]; ?>
        <div class="gig-notice gig-notice-<?php echo $mtype === 'success' ? 'success' : 'error'; ?>" style="margin:16px 28px 0;">
            <?php if ($mtype==='success') : ?>
                <svg width="15" height="15" fill="none" viewBox="0 0 16 16"><circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M5 8l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <?php endif; ?>
            <?php echo esc_html( $mtext ); ?>
        </div>
    <?php endif; ?>