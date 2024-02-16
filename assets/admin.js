// any CSS you import will output into a single css file (app.css in this case)
import './styles/Admin/index.css';
import './styles/Admin/header.css';
import './styles/Admin/home.css';
import './styles/Admin/form.css';
import './styles/Admin/form_filters.css';
import './styles/Admin/pagination.css';
import './styles/Admin/table.css';
import './styles/Admin/UI/buttons.css';
import './styles/Admin/flashes.css';
import './styles/Admin/breadcrumb.css';
import './styles/Admin/Product/show.css';

// start the Stimulus application
import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { SuggestedProductsInput } from './Components/Admin/SuggestedProductsInput';
import { PurchaseStatusUpdater } from './Components/Admin/PurchaseStatusUpdater';
import { ReviewModerator } from './Components/Admin/ReviewModerator';



if(document.getElementById('suggested-products-input')) {
    const suggestedProductInput = document.getElementById('suggested-products-input');
    const suggestedProductInputRoot = createRoot(suggestedProductInput);

    suggestedProductInputRoot.render(
        <SuggestedProductsInput productId={suggestedProductInput.dataset?.productid} />
    );
}

if(document.getElementById('purchase-status-updater')) {
    const purchaseStatusUpdater = document.getElementById('purchase-status-updater');
    const purchaseStatusUpdaterRoot = createRoot(purchaseStatusUpdater);

    purchaseStatusUpdaterRoot.render(
        <PurchaseStatusUpdater id={purchaseStatusUpdater.dataset.id} initialStatus={purchaseStatusUpdater.dataset.status} />
    );
}

if(document.getElementById('review-moderator')) {
    const reviewModerator = document.getElementById('review-moderator');
    const reviewModeratorRoot = createRoot(reviewModerator);

    reviewModeratorRoot.render(
        <ReviewModerator id={reviewModerator.dataset.id} initialStatusLabel={reviewModerator.dataset.statuslabel} />
    )
}