<tr data-review-criterion>
    <td><input class="form-control form-control-sm" name="criteria[]" value="{{ $criteria }}" maxlength="255" aria-label="{{ __('index.criteria') }}" required></td>
    <td><input class="form-control form-control-sm" name="description[]" value="{{ $description }}" aria-label="{{ __('index.standard') }}"></td>
    <td><input class="form-control form-control-sm" type="number" name="max_score[]" value="{{ $max }}" min="0" step="0.01" data-review-max aria-label="{{ __('index.max') }}" required></td>
    <td><input class="form-control form-control-sm" type="number" name="score[]" value="{{ $score }}" min="0" max="{{ $max }}" step="0.01" data-review-score aria-label="{{ __('index.score') }}" required></td>
    <td><input class="form-control form-control-sm" name="comment[]" value="{{ $comment }}" placeholder="{{ __('index.comment') }}" aria-label="{{ __('index.comment') }}"></td>
    <td><button type="button" class="btn btn-outline-danger btn-sm" data-remove-review-criterion>{{ __('index.remove_review_criterion') }}</button></td>
</tr>
