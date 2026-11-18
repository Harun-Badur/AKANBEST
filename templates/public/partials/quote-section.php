<?php
declare(strict_types=1);

$quoteErrors = $quoteErrors ?? [];
$quoteOld = $quoteOld ?? [];
$quoteSuccess = $quoteSuccess ?? '';
$quoteWhatsapp = $quoteWhatsapp ?? '';
$quoteIcons = [
    'person' => '<circle cx="12" cy="7" r="3"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
    'company' => '<path d="M3 21V3h11v18M14 9h7v12M7 7h3M7 11h3M7 15h3M18 13v2M18 18v2M2 21h20"/>',
    'phone' => '<path d="m7 3 3 5-3 3a17 17 0 0 0 6 6l3-3 5 3-1 4C10 22 2 14 3 4z"/>',
    'email' => '<rect x="3" y="5" width="18" height="14" rx="1"/><path d="m3 6 9 7 9-7"/>',
    'box' => '<path d="m3 7 9-5 9 5v10l-9 5-9-5zM3 7l9 5 9-5M12 12v10"/>',
    'layers' => '<path d="m2 7 10-5 10 5-10 5zM2 12l10 5 10-5M2 17l10 5 10-5"/>',
    'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 2v6M17 2v6M3 11h18"/>',
    'message' => '<path d="M4 3h16v14H9l-5 4zM8 7h8M8 11h5"/>',
    'send' => '<path d="m22 2-7 20-4-9-9-4zM22 2 11 13"/>',
    'pin' => '<path d="M19 10c0 5-7 12-7 12S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2"/>',
];
$quoteIcon = static fn (string $name): string => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $quoteIcons[$name] . '</svg>';
$quoteField = static function (string $name, string $label, string $icon, string $type = 'text', bool $required = false, int $max = 200) use ($quoteOld, $quoteErrors, $quoteIcon): void { ?>
  <div class="quote-field">
    <label for="quote-<?= e($name) ?>"><?= e($label) ?><?= $required ? ' *' : '' ?></label>
    <div class="quote-input"><?= $quoteIcon($icon) ?><input id="quote-<?= e($name) ?>" name="<?= e($name) ?>" type="<?= e($type) ?>" value="<?= e((string) ($quoteOld[$name] ?? '')) ?>"<?= $required ? ' required' : '' ?><?= $type === 'number' ? ' min="1" max="2147483647" step="1"' : ' maxlength="' . $max . '"' ?><?= isset($quoteErrors[$name]) ? ' aria-invalid="true" aria-describedby="quote-error-' . e($name) . '"' : '' ?> autocomplete="<?= e(match ($name) { 'full_name' => 'name', 'company' => 'organization', 'phone' => 'tel', 'email' => 'email', default => 'off' }) ?>"></div>
    <?php if (isset($quoteErrors[$name])): ?><p class="quote-error" id="quote-error-<?= e($name) ?>"><?= e((string) $quoteErrors[$name]) ?></p><?php endif; ?>
  </div>
<?php }; ?>
<section class="quote-section" id="teklif" aria-labelledby="quote-heading">
  <div class="quote-main">
    <div class="quote-grid">
      <div class="quote-intro">
        <span class="quote-eyebrow">TEKLİF AL</span>
        <h2 id="quote-heading">İhtiyaçlarınıza özel <span>ambalaj çözümü</span> sunuyoruz.</h2>
        <p>İlgilendiğiniz ürünü, miktarı ve kullanım alanını paylaşın. Ambalaj ihtiyacınızı birlikte değerlendirmek için teklif formunu doldurun.</p>
        <ul class="quote-benefits">
          <li><span class="quote-benefit-icon"><?= $quoteIcon('box') ?></span><h3>Size Özel Teklif</h3><p>Ürününüzü ve ihtiyacınızı belirtin.</p></li>
          <li><span class="quote-benefit-icon"><?= $quoteIcon('layers') ?></span><h3>Net İhtiyaç Planı</h3><p>Miktar ve üretim beklentinizi paylaşın.</p></li>
          <li><span class="quote-benefit-icon"><?= $quoteIcon('message') ?></span><h3>Doğrudan İletişim</h3><p>Talebinizi doğrudan bize iletin.</p></li>
        </ul>
      </div>
      <div class="quote-panel">
        <h3>Teklif Talebi</h3>
        <p class="quote-panel-lead">Size uygun ambalaj çözümü ve fiyat teklifi için aşağıdaki bilgileri doldurun.</p>
        <?php if ($quoteSuccess !== ''): ?><p class="quote-feedback quote-feedback--success" role="status"><?= e((string) $quoteSuccess) ?></p><?php if ($quoteWhatsapp !== ''): ?><p class="quote-whatsapp"><a class="btn btn--accent" href="<?= e($quoteWhatsapp) ?>" target="_blank" rel="noopener noreferrer">WhatsApp ile ilet</a></p><?php endif; ?><?php endif; ?>
        <?php if ($quoteErrors !== []): ?><div class="quote-feedback" role="alert"><?= e((string) ($quoteErrors['form'] ?? 'Lütfen aşağıdaki alanları kontrol ederek talebinizi yeniden gönderin.')) ?></div><?php endif; ?>
        <form class="quote-form" method="post" action="/teklif">
          <?= \App\Core\Csrf::field() ?>
          <div class="quote-hp" aria-hidden="true">
            <label for="quote-website">Website</label>
            <input id="quote-website" name="website" type="text" value="" tabindex="-1" autocomplete="off">
          </div>
          <?php $quoteField('full_name', 'Ad Soyad', 'person', 'text', true, 201); ?>
          <?php $quoteField('company', 'Firma Adı', 'company', 'text', true); ?>
          <?php $quoteField('phone', 'Telefon', 'phone', 'tel', true, 50); ?>
          <?php $quoteField('email', 'E-posta', 'email', 'email', true, 255); ?>
          <div class="quote-field">
            <label for="quote-product_id">İlgilendiğiniz Ürün *</label>
            <div class="quote-input"><?= $quoteIcon('box') ?><select id="quote-product_id" name="product_id" required<?= isset($quoteErrors['product_id']) ? ' aria-invalid="true" aria-describedby="quote-error-product_id"' : '' ?>>
              <option value="">Ürün seçin</option>
              <?php foreach ($products as $quoteProduct): ?><option value="<?= (int) $quoteProduct['id'] ?>"<?= (string) ($quoteOld['product_id'] ?? '') === (string) $quoteProduct['id'] ? ' selected' : '' ?>><?= e((string) $quoteProduct['name']) ?></option><?php endforeach; ?>
            </select></div>
            <?php if (isset($quoteErrors['product_id'])): ?><p class="quote-error" id="quote-error-product_id"><?= e((string) $quoteErrors['product_id']) ?></p><?php endif; ?>
          </div>
          <?php $quoteField('production_type', 'Üretim Türü (opsiyonel)', 'layers'); ?>
          <?php $quoteField('quantity', 'Tahmini Miktar', 'box', 'number', true); ?>
          <?php $quoteField('request_date', 'Talep Zamanı (opsiyonel)', 'calendar', 'date', false, 10); ?>
          <div class="quote-field quote-field--wide">
            <label for="quote-message">Ek Bilgi (opsiyonel)</label>
            <div class="quote-input quote-input--message"><?= $quoteIcon('message') ?><textarea id="quote-message" name="message" rows="3" maxlength="3000" placeholder="Ölçü, kullanım alanı veya özel taleplerinizi paylaşabilirsiniz."<?= isset($quoteErrors['message']) ? ' aria-invalid="true" aria-describedby="quote-error-message"' : '' ?>><?= e((string) ($quoteOld['message'] ?? '')) ?></textarea></div>
            <?php if (isset($quoteErrors['message'])): ?><p class="quote-error" id="quote-error-message"><?= e((string) $quoteErrors['message']) ?></p><?php endif; ?>
          </div>
          <button class="btn btn--accent quote-submit" type="submit"<?= $products === [] ? ' disabled' : '' ?>><?= $quoteIcon('send') ?> WhatsApp ile Teklif Gönder <span aria-hidden="true">→</span></button>
        </form>
        <p class="quote-panel-lead">Gönder'e bastığınızda WhatsApp açılır, mesajı göndererek teklifinizi iletebilirsiniz.</p>
      </div>
    </div>
  </div>
  <div class="quote-contact">
    <div class="site-container quote-contact-grid">
      <div class="quote-contact-intro"><span class="quote-contact-eyebrow">İLETİŞİM</span><h3>İhtiyacınızı <span>bizimle paylaşın.</span></h3><p>Ürünlerimiz, fiyatlandırma ve özel talepleriniz için teklif formunu kullanabilirsiniz.</p></div>
      <?php if (\App\Core\Contact::phone() !== ''): ?><div class="quote-contact-item"><span class="quote-contact-icon"><?= $quoteIcon('phone') ?></span><h4>Telefon</h4><a href="tel:<?= e(preg_replace('/[^\d+]/', '', \App\Core\Contact::phone()) ?? '') ?>"><?= e(\App\Core\Contact::phone()) ?></a></div><?php endif; ?>
      <?php if (\App\Core\Contact::email() !== ''): ?><div class="quote-contact-item"><span class="quote-contact-icon"><?= $quoteIcon('email') ?></span><h4>E-posta</h4><a href="mailto:<?= e(\App\Core\Contact::email()) ?>"><?= e(\App\Core\Contact::email()) ?></a></div><?php endif; ?>
      <?php if (\App\Core\Contact::address() !== ''): ?><div class="quote-contact-item"><span class="quote-contact-icon"><?= $quoteIcon('pin') ?></span><h4>Adres</h4><address><?= e(\App\Core\Contact::address()) ?></address></div><?php endif; ?>
    </div>
  </div>
</section>
