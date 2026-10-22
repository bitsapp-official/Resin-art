<x-app-layout :title="$product->name . ' — Solitaire Fine Jewels'">
<div class="min-h-screen bg-transparent w-full min-w-0">
    @php
        $mediaList = [];
        $colorGalleries = $product->color_galleries ?: [];
        $rawProductImages = !empty($product->images) ? (array) $product->images : [];

        // Match initial color from query param ?color= if provided
        $reqColor = request('color');
        $matchedReqColor = null;
        if ($reqColor && !empty($product->metal_colors)) {
            foreach ($product->metal_colors as $mc) {
                if (strcasecmp($mc, $reqColor) === 0) {
                    $matchedReqColor = $mc;
                    break;
                }
            }
        }
        $firstColor = $matchedReqColor ?: (!empty($product->metal_colors) ? ($product->metal_colors[0] ?? 'Yellow Gold') : 'Yellow Gold');

        $addedUrls = [];
        $allMedia = [];

        // Helper to extract a clean unique deduplication key for images
        $getMediaKey = function ($url) {
            if (empty($url) || !is_string($url)) return null;
            $path = parse_url($url, PHP_URL_PATH) ?: $url;
            return strtolower(basename($path));
        };

        // 1. Collect photos for the primary/default metal color
        $primaryColorPhotos = [];
        if (!empty($colorGalleries[$firstColor])) {
            foreach ((array) $colorGalleries[$firstColor] as $cUrl) {
                if (!empty($cUrl)) {
                    $key = $getMediaKey($cUrl);
                    if ($key && empty($addedUrls[$key])) {
                        $primaryColorPhotos[] = [
                            'type' => 'image',
                            'src' => $cUrl,
                            'thumb' => $cUrl,
                            'color' => $firstColor,
                        ];
                        $addedUrls[$key] = true;
                    }
                }
            }
        }

        // 2. Video item
        $videoItem = null;
        if ($product->has_video) {
            $hasUpload = !empty($product->video_path);
            $videoSrc = $product->video_asset_url;
            $isYoutube = !$hasUpload && !empty($product->youtube_id);
            $isVimeo = !$hasUpload && !$isYoutube && !empty($product->vimeo_id);
            $embedUrl = $isYoutube ? $product->youtube_embed_url : ($isVimeo ? $product->vimeo_embed_url : null);
            $videoThumb = $product->video_thumbnail_url;

            $videoItem = [
                'type' => 'video',
                'src' => $videoSrc,
                'thumb' => $videoThumb,
                'isUpload' => $hasUpload,
                'isYoutube' => $isYoutube,
                'isVimeo' => $isVimeo,
                'embedUrl' => $embedUrl,
                'originalUrl' => $product->video_url,
                'color' => null,
            ];
        }

        // Position 1: First photo of primary color (or first general photo if piece has no metal color photos)
        $firstGeneralPhoto = null;
        if (!empty($primaryColorPhotos)) {
            $allMedia[] = $primaryColorPhotos[0];
        } elseif (!empty($rawProductImages)) {
            foreach ($rawProductImages as $pImg) {
                if (!empty($pImg)) {
                    $key = $getMediaKey($pImg);
                    if ($key && empty($addedUrls[$key])) {
                        $firstGeneralPhoto = [
                            'type' => 'image',
                            'src' => $pImg,
                            'thumb' => $pImg,
                            'color' => null,
                        ];
                        $allMedia[] = $firstGeneralPhoto;
                        $addedUrls[$key] = true;
                        break;
                    }
                }
            }
        }

        // Position 2: Video Showcase (if product has video)
        if ($videoItem) {
            $allMedia[] = $videoItem;
        }

        // Position 3+: Remaining photos of primary color
        if (count($primaryColorPhotos) > 1) {
            for ($i = 1; $i < count($primaryColorPhotos); $i++) {
                $allMedia[] = $primaryColorPhotos[$i];
            }
        }

        // 3. Add photos from all other metal colors
        foreach ($colorGalleries as $cName => $cList) {
            if ($cName === $firstColor) continue;
            foreach ((array) $cList as $cUrl) {
                if (!empty($cUrl)) {
                    $key = $getMediaKey($cUrl);
                    if ($key && empty($addedUrls[$key])) {
                        $allMedia[] = [
                            'type' => 'image',
                            'src' => $cUrl,
                            'thumb' => $cUrl,
                            'color' => $cName,
                        ];
                        $addedUrls[$key] = true;
                    }
                }
            }
        }

        // 4. Add any additional gallery photos that are not already added
        foreach ($rawProductImages as $pImg) {
            if (!empty($pImg)) {
                $key = $getMediaKey($pImg);
                if ($key && empty($addedUrls[$key])) {
                    // Check if this image matches any color
                    $matchedColor = null;
                    foreach ($colorGalleries as $cName => $cList) {
                        foreach ((array) $cList as $cUrl) {
                            if ($getMediaKey($cUrl) === $key) {
                                $matchedColor = $cName;
                                break 2;
                            }
                        }
                    }
                    $allMedia[] = [
                        'type' => 'image',
                        'src' => $pImg,
                        'thumb' => $pImg,
                        'color' => $matchedColor,
                    ];
                    $addedUrls[$key] = true;
                }
            }
        }

        $mediaList = $allMedia;
    @endphp
    <script>
        function productDetail(config) {
            return {
                rawMediaItems: config.mediaItems || [],
                activeMedia: 0,
                selectedSize: config.selectedSize || '',
                sizeLabel: config.sizeLabel || 'Size',
                sizeVariants: config.sizeVariants || [],
                goldPurities: (config.goldPurities && Array.isArray(config.goldPurities) && config.goldPurities.length > 0) ? config.goldPurities : [],
                selectedPurity: config.selectedPurity || ((config.goldPurities && config.goldPurities.length > 0) ? config.goldPurities[0] : ''),
                metalColors: config.metalColors || ['Yellow Gold', 'White Gold', 'Rose Gold'],
                selectedColor: config.selectedColor || 'Yellow Gold',
                colorImages: config.colorImages || {},
                productTitle: config.productTitle || '',
                productSku: config.productSku || '',
                whatsappNumber: config.whatsappNumber || '919876543210',
                qty: 1,
                showLightbox: false,
                basePrice: config.basePrice || 0,
                
                // Lightbox cut-free zoom & pan
                lightboxZoom: 1,
                lightboxPanX: 0,
                lightboxPanY: 0,
                isDragging: false,
                dragStartX: 0,
                dragStartY: 0,

                // Dynamic Solitairekart-style gallery:
                // Pos 1: Primary front photo of selected metal color
                // Pos 2: Product Showcase Video (if available)
                // Pos 3+: Remaining angle photos of selected color, followed by other metal colors
                // Pos Last: General Photos (Templates, Size Chart, Dimensions)
                get mediaItems() {
                    const videoItems = (this.rawMediaItems || []).filter(m => m && m.type === 'video');
                    const generalItems = (this.rawMediaItems || []).filter(m => m && m.type !== 'video' && !m.color);
                    const colorItems = (this.rawMediaItems || []).filter(m => m && m.color);

                    // Case A: Pure Single Piece or Product with NO color variants at all
                    if (colorItems.length === 0) {
                        const firstGeneral = generalItems.slice(0, 1);
                        const restGeneral = generalItems.slice(1);
                        return [...firstGeneral, ...videoItems, ...restGeneral];
                    }

                    if (!this.selectedColor || !this.rawMediaItems || this.rawMediaItems.length === 0) {
                        const firstColor = colorItems.slice(0, 1);
                        const restColor = colorItems.slice(1);
                        return [...firstColor, ...videoItems, ...restColor, ...generalItems];
                    }
                    const sel = (this.selectedColor || '').toLowerCase().trim();
                    const activeColorItems = this.rawMediaItems.filter(m => m && m.color && m.color.toLowerCase().trim() === sel);
                    const otherColorItems = this.rawMediaItems.filter(m => m && m.color && m.color.toLowerCase().trim() !== sel);

                    if (activeColorItems.length === 0) {
                        if (otherColorItems.length > 0) {
                            const firstOther = otherColorItems.slice(0, 1);
                            const restOther = otherColorItems.slice(1);
                            return [...firstOther, ...videoItems, ...restOther, ...generalItems];
                        }
                        const firstGeneral = generalItems.slice(0, 1);
                        const restGeneral = generalItems.slice(1);
                        return [...firstGeneral, ...videoItems, ...restGeneral];
                    }

                    const firstActive = activeColorItems.slice(0, 1);
                    const restActive = activeColorItems.slice(1);

                    return [...firstActive, ...videoItems, ...restActive, ...otherColorItems, ...generalItems];
                },

                get currentMedia() {
                    return this.mediaItems[this.activeMedia] || null;
                },
                
                canScrollThumbLeft: false,
                canScrollThumbRight: false,

                init() {
                    window.addEventListener('touchmove', (e) => {
                        if (this.showLightbox && (this.lightboxZoom <= 1 || !e.target.closest('[x-ref="lightboxImg"]'))) {
                            e.preventDefault();
                        }
                    }, { passive: false });

                    window.addEventListener('resize', () => {
                        this.updateThumbScrollState();
                    });

                    this.activeMedia = 0;
                    this.$nextTick(() => {
                        this.updateThumbScrollState();
                    });
                    setTimeout(() => this.updateThumbScrollState(), 200);
                    setTimeout(() => this.updateThumbScrollState(), 600);

                    // Dynamically synchronize browser URL whenever color, purity, or size is switched
                    this.$watch('selectedColor', () => this.syncUrlWithState());
                    this.$watch('selectedPurity', () => this.syncUrlWithState());
                    this.$watch('selectedSize', () => this.syncUrlWithState());

                    // Check URL query parameters on initial page load
                    try {
                        const urlParams = new URLSearchParams(window.location.search);
                        const qColor = urlParams.get('color');
                        const qPurity = urlParams.get('purity');
                        const qSize = urlParams.get('size');
                        if (qColor) {
                            const foundColor = (this.metalColors || []).find(c => c.toLowerCase().trim() === qColor.toLowerCase().trim());
                            if (foundColor) this.selectedColor = foundColor;
                        }
                        if (qPurity) {
                            const foundPurity = (this.goldPurities || []).find(p => p.toLowerCase().trim() === qPurity.toLowerCase().trim() || p.toLowerCase().includes(qPurity.toLowerCase().trim()));
                            if (foundPurity) this.selectedPurity = foundPurity;
                        }
                        if (qSize) {
                            const foundSize = (this.sizeVariants || []).find(v => (v.size || '').toLowerCase().trim() === qSize.toLowerCase().trim());
                            if (foundSize) this.selectedSize = foundSize.size;
                        }
                    } catch(e) {}

                    // Initial URL sync so shareable query params are in the address bar
                    this.syncUrlWithState();
                },

                syncUrlWithState() {
                    try {
                        const url = new URL(window.location.href);
                        if (this.selectedColor) {
                            url.searchParams.set('color', this.selectedColor);
                        } else {
                            url.searchParams.delete('color');
                        }
                        if (this.selectedPurity) {
                            url.searchParams.set('purity', this.selectedPurity);
                        } else {
                            url.searchParams.delete('purity');
                        }
                        if (this.selectedSize) {
                            url.searchParams.set('size', this.selectedSize);
                        } else {
                            url.searchParams.delete('size');
                        }
                        window.history.replaceState({}, '', url.pathname + url.search + url.hash);
                    } catch(e) {}
                },

                updateThumbScrollState() {
                    const el = this.$refs.thumbContainer;
                    if (!el) return;
                    this.canScrollThumbLeft = el.scrollLeft > 4;
                    this.canScrollThumbRight = Math.ceil(el.scrollLeft + el.clientWidth) < (el.scrollWidth - 4);
                },

                scrollThumbLeft() {
                    const el = this.$refs.thumbContainer;
                    if (el) {
                        el.scrollBy({ left: -220, behavior: 'smooth' });
                        setTimeout(() => this.updateThumbScrollState(), 320);
                    }
                },

                scrollThumbRight() {
                    const el = this.$refs.thumbContainer;
                    if (el) {
                        el.scrollBy({ left: 220, behavior: 'smooth' });
                        setTimeout(() => this.updateThumbScrollState(), 320);
                    }
                },

                scrollActiveThumbIntoView() {
                    this.$nextTick(() => {
                        const el = this.$refs.thumbContainer;
                        if (!el) return;
                        const activeBtn = el.querySelector('[data-thumb-idx="' + this.activeMedia + '"]');
                        if (activeBtn) {
                            activeBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                        }
                        setTimeout(() => this.updateThumbScrollState(), 320);
                    });
                },

                isVideoPlaying: false,

                pauseAllVideos() {
                    this.isVideoPlaying = false;
                    document.querySelectorAll('video').forEach(v => {
                        try {
                            v.pause();
                        } catch(e) {}
                    });
                },
                
                openLightbox() {
                    this.pauseAllVideos();
                    this.showLightbox = true;
                    this.resetLightboxZoom();
                    document.documentElement.classList.add('overflow-hidden', 'touch-none');
                    document.body.classList.add('overflow-hidden', 'touch-none');
                    document.documentElement.style.overflow = 'hidden';
                    document.documentElement.style.overscrollBehavior = 'none';
                    document.body.style.overflow = 'hidden';
                    document.body.style.overscrollBehavior = 'none';
                    document.body.style.touchAction = 'none';
                    this.$nextTick(() => {
                        this.playActiveVideo();
                    });
                },
                closeLightbox() {
                    this.pauseAllVideos();
                    this.showLightbox = false;
                    this.resetLightboxZoom();
                    document.documentElement.classList.remove('overflow-hidden', 'touch-none');
                    document.body.classList.remove('overflow-hidden', 'touch-none');
                    document.documentElement.style.overflow = '';
                    document.documentElement.style.overscrollBehavior = '';
                    document.body.style.overflow = '';
                    document.body.style.overscrollBehavior = '';
                    document.body.style.touchAction = '';
                },
                resetLightboxZoom() {
                    this.lightboxZoom = 1;
                    this.lightboxPanX = 0;
                    this.lightboxPanY = 0;
                    this.isDragging = false;
                },
                zoomIn() {
                    if (this.lightboxZoom < 3) {
                        this.lightboxZoom = +(this.lightboxZoom + 0.5).toFixed(1);
                    }
                },
                zoomOut() {
                    if (this.lightboxZoom > 1) {
                        this.lightboxZoom = +(this.lightboxZoom - 0.5).toFixed(1);
                        if (this.lightboxZoom <= 1) {
                            this.resetLightboxZoom();
                        }
                    }
                },
                toggleLightboxZoom() {
                    if (this.lightboxZoom === 1) {
                        this.lightboxZoom = 2;
                    } else {
                        this.resetLightboxZoom();
                    }
                },
                handleWheel(e) {
                    if (!this.showLightbox) return;
                    if (this.currentMedia && this.currentMedia.type === 'video') return;
                    e.preventDefault();
                    if (e.deltaY < 0) {
                        this.zoomIn();
                    } else {
                        this.zoomOut();
                    }
                },
                startDrag(e) {
                    if (this.lightboxZoom <= 1) return;
                    this.isDragging = true;
                    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                    this.dragStartX = clientX - this.lightboxPanX;
                    this.dragStartY = clientY - this.lightboxPanY;
                },
                onDrag(e) {
                    if (!this.isDragging || this.lightboxZoom <= 1) return;
                    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                    const img = this.$refs.lightboxImg;
                    const w = img ? img.offsetWidth : 600;
                    const h = img ? img.offsetHeight : 600;
                    const maxPanX = Math.max(120, ((w * this.lightboxZoom) - w) / 2 + 100);
                    const maxPanY = Math.max(120, ((h * this.lightboxZoom) - h) / 2 + 100);
                    const newX = clientX - this.dragStartX;
                    const newY = clientY - this.dragStartY;
                    this.lightboxPanX = Math.max(-maxPanX, Math.min(maxPanX, newX));
                    this.lightboxPanY = Math.max(-maxPanY, Math.min(maxPanY, newY));
                },
                stopDrag() {
                    this.isDragging = false;
                },
                
                // ── DYNAMIC JEWELRY PRICING MATRIX ENGINE ──
                pricingMode: config.pricingMode || 'manual',
                baseGoldWeight: Number(config.baseGoldWeight) || 0,
                makingChargePerGram: Number(config.makingChargePerGram) || 0,
                diamondStoneValue: Number(config.diamondStoneValue) || 0,
                profitMarginPercent: Number(config.profitMarginPercent) || 0,
                rate24kt: Number(config.rate24kt) || 7600,
                bullionRates: config.bullionRates || {},
                commonCutPrice: Number(config.commonCutPrice) || 0,
                regularPrice: Number(config.regularPrice) || 0,

                getDensityMultiplier(purity) {
                    if (!purity) return 1.185;
                    const p = purity.toLowerCase().trim();
                    if (p.includes('14k')) return 1.000;
                    if (p.includes('22k')) return 1.365;
                    return 1.185; // 18KT benchmark
                },

                getSizeMultiplier(size) {
                    if (!size) return 1.000;
                    const match = String(size).match(/([\d\.]+)/);
                    if (match) {
                        const num = parseFloat(match[1]);
                        if (num >= 5 && num <= 25) {
                            return 1.0 + ((num - 12.0) * 0.015);
                        }
                    }
                    return 1.000;
                },

                getKaratGoldRate(purity) {
                    const rates = this.bullionRates || {};
                    const p = (purity || '').toLowerCase().trim();
                    if (p.includes('14k')) {
                        return rates.rate_14kt ? Number(rates.rate_14kt) : Math.round((this.rate24kt || 7600) * (14 / 24) * 100) / 100;
                    }
                    if (p.includes('22k')) {
                        return rates.rate_22kt ? Number(rates.rate_22kt) : Math.round((this.rate24kt || 7600) * (22 / 24) * 100) / 100;
                    }
                    return rates.rate_18kt ? Number(rates.rate_18kt) : Math.round((this.rate24kt || 7600) * (18 / 24) * 100) / 100;
                },

                get priceBreakdown() {
                    // Check if product uses Dynamic Gold Pricing Matrix
                    if (this.baseGoldWeight > 0 && this.pricingMode === 'dynamic' && this.goldPurities && this.goldPurities.length > 0) {
                        const purity = this.selectedPurity || this.goldPurities[0] || '18KT Gold';
                        const size = this.selectedSize || 'Size 12';
                        
                        // Check if current selected size has explicit custom weight
                        let sizeVariant = null;
                        if (this.sizeVariants && this.sizeVariants.length > 0) {
                            sizeVariant = this.sizeVariants.find(v => v.size === size);
                        }

                        // Exact gold weight: use size-specific weight if provided, else product base weight.
                        // No artificial scale multipliers or density weight distortions.
                        let finalWeight = 0;
                        if (sizeVariant && sizeVariant.weight_grams && Number(sizeVariant.weight_grams) > 0) {
                            finalWeight = Number(sizeVariant.weight_grams);
                        } else {
                            finalWeight = this.baseGoldWeight;
                        }
                        finalWeight = Math.round(finalWeight * 1000) / 1000;

                        const karatRate = this.getKaratGoldRate(purity);

                        const goldVal = Math.round(finalWeight * karatRate * 100) / 100;
                        const makingVal = Math.round(finalWeight * this.makingChargePerGram * 100) / 100;
                        const stoneVal = Math.round(this.diamondStoneValue * 100) / 100;
                        const subtotal = Math.round((goldVal + makingVal + stoneVal) * 100) / 100;
                        const gst = Math.round(subtotal * 0.03 * 100) / 100;
                        const costWithGst = Math.round((subtotal + gst) * 100) / 100;
                        const margin = Number(this.profitMarginPercent) || 0;
                        const profitVal = Math.round(costWithGst * (margin / 100) * 100) / 100;
                        const finalPrice = Math.round(costWithGst + profitVal);

                        // If common Cut Price is set by admin, it remains the SAME across all sizes and purities
                        let mrp = null;
                        if (this.commonCutPrice && Number(this.commonCutPrice) > 0) {
                            mrp = Number(this.commonCutPrice);
                        } else if (sizeVariant) {
                            const pLower = (purity || '').toLowerCase();
                            if (pLower.includes('14') && sizeVariant.mrp_14k && Number(sizeVariant.mrp_14k) > 0) {
                                mrp = Number(sizeVariant.mrp_14k);
                            } else if (pLower.includes('22') && sizeVariant.mrp_22k && Number(sizeVariant.mrp_22k) > 0) {
                                mrp = Number(sizeVariant.mrp_22k);
                            } else if (sizeVariant.mrp_18k && Number(sizeVariant.mrp_18k) > 0) {
                                mrp = Number(sizeVariant.mrp_18k);
                            } else if (sizeVariant.mrp && Number(sizeVariant.mrp) > finalPrice) {
                                mrp = Number(sizeVariant.mrp);
                            }
                        }

                        if (!mrp && this.regularPrice && Number(this.regularPrice) > finalPrice) {
                            mrp = Number(this.regularPrice);
                        }

                        return {
                            isDynamic: true,
                            finalWeight: finalWeight,
                            goldValue: goldVal,
                            makingValue: makingVal,
                            diamondValue: stoneVal,
                            subtotal: subtotal,
                            gstAmount: gst,
                            profitAmount: profitVal,
                            finalPrice: finalPrice,
                            mrp: mrp
                        };
                    }

                    // Fallback to Manual Pricing Mode
                    let base = Number(this.basePrice) || 0;
                    let sizeVariant = null;
                    if (this.sizeVariants.length > 0 && this.selectedSize) {
                        sizeVariant = this.sizeVariants.find(v => v.size === this.selectedSize);
                        if (sizeVariant) {
                            const pLower = (this.selectedPurity || '').toLowerCase();
                            if (pLower.includes('14') && sizeVariant.price_14k && Number(sizeVariant.price_14k) > 0) {
                                base = Number(sizeVariant.price_14k);
                            } else if (pLower.includes('22') && sizeVariant.price_22k && Number(sizeVariant.price_22k) > 0) {
                                base = Number(sizeVariant.price_22k);
                            } else if (sizeVariant.price_18k && Number(sizeVariant.price_18k) > 0) {
                                base = Number(sizeVariant.price_18k);
                            } else if (sizeVariant.price && Number(sizeVariant.price) > 0) {
                                base = Number(sizeVariant.price);
                            }
                        }
                    }

                    if (this.purityPrices && this.selectedPurity && this.purityPrices[this.selectedPurity] !== undefined && this.purityPrices[this.selectedPurity] !== null && this.purityPrices[this.selectedPurity] !== '') {
                        const val = Number(this.purityPrices[this.selectedPurity]);
                        if (val >= base * 0.5 && val > 0) {
                            base = val;
                        } else {
                            base += val;
                        }
                    }

                    let mrp = null;
                    if (this.commonCutPrice && Number(this.commonCutPrice) > 0) {
                        mrp = Number(this.commonCutPrice);
                    } else if (sizeVariant) {
                        const pLower = (this.selectedPurity || '').toLowerCase();
                        if (pLower.includes('14') && sizeVariant.mrp_14k && Number(sizeVariant.mrp_14k) > 0) {
                            mrp = Number(sizeVariant.mrp_14k);
                        } else if (pLower.includes('22') && sizeVariant.mrp_22k && Number(sizeVariant.mrp_22k) > 0) {
                            mrp = Number(sizeVariant.mrp_22k);
                        } else if (sizeVariant.mrp_18k && Number(sizeVariant.mrp_18k) > 0) {
                            mrp = Number(sizeVariant.mrp_18k);
                        } else if (sizeVariant.mrp && Number(sizeVariant.mrp) > base) {
                            mrp = Number(sizeVariant.mrp);
                        }
                    }
                    if (!mrp && this.purityMrp && this.selectedPurity && this.purityMrp[this.selectedPurity] !== undefined && this.purityMrp[this.selectedPurity] !== null && this.purityMrp[this.selectedPurity] !== '') {
                        const mVal = Number(this.purityMrp[this.selectedPurity]);
                        if (mVal > 0) mrp = mVal;
                    }
                    if (!mrp) {
                        const reg = Number(this.regularPrice) || 0;
                        if (reg > base) mrp = reg;
                    }

                    return {
                        isDynamic: false,
                        finalPrice: base,
                        mrp: mrp
                    };
                },
                
                get currentPrice() {
                    return this.priceBreakdown.finalPrice;
                },

                get currentGoldWeight() {
                    if (this.priceBreakdown && this.priceBreakdown.finalWeight) {
                        return this.priceBreakdown.finalWeight;
                    }
                    if (this.sizeVariants.length > 0 && this.selectedSize) {
                        const sv = this.sizeVariants.find(v => v.size === this.selectedSize);
                        if (sv && sv.weight_grams && Number(sv.weight_grams) > 0) {
                            return Number(sv.weight_grams);
                        }
                    }
                    return Number(this.baseGoldWeight) || 0;
                },

                get currentMrp() {
                    return this.priceBreakdown.mrp;
                },

                get discountPercent() {
                    if (this.currentMrp && this.currentMrp > this.currentPrice) {
                        return Math.round(((this.currentMrp - this.currentPrice) / this.currentMrp) * 100);
                    }
                    return 0;
                },
                formatPrice(price) {
                    if (price === null || price === undefined || isNaN(price)) {
                        return this.basePrice ? new Intl.NumberFormat('en-IN').format(this.basePrice) : '0';
                    }
                    return new Intl.NumberFormat('en-IN').format(price);
                },
                get whatsappOrderUrl() {
                    let msg = `Hello, I would like to order / inquire about this piece:\n\n` +
                              `• Product: ${this.productTitle}\n` +
                              (this.productSku ? `• SKU: ${this.productSku}\n` : '') +
                              (this.selectedColor ? `• Metal Color: ${this.selectedColor}\n` : '') +
                              (this.selectedPurity ? `• Gold Purity: ${this.selectedPurity}\n` : '') +
                              (this.selectedSize ? `• ${this.sizeLabel}: ${this.selectedSize}\n` : '') +
                              `• Quantity: ${this.qty}\n` +
                              `• Price: ₹ ${this.formatPrice(this.currentPrice * this.qty)}\n\n` +
                              `Link: ${window.location.href}`;
                    return `https://wa.me/${this.whatsappNumber}?text=` + encodeURIComponent(msg);
                },
                get whatsappBulkUrl() {
                    let msg = `Hello, I am interested in placing a Bulk / Wholesale B2B inquiry for:\n\n` +
                              `• Product: ${this.productTitle}\n` +
                              (this.productSku ? `• SKU: ${this.productSku}\n` : '') +
                              `Link: ${window.location.href}\n\n` +
                              `Please share bulk quantity pricing, diamond certification details, and dispatch lead times.`;
                    return `https://wa.me/${this.whatsappNumber}?text=` + encodeURIComponent(msg);
                },
                getColorStyle(color) {
                    const c = (color || '').toLowerCase();
                    if (c.includes('yellow')) return 'background: linear-gradient(135deg, #FFE082 0%, #D4AF37 50%, #B8860B 100%);';
                    if (c.includes('rose'))   return 'background: linear-gradient(135deg, #FAD0C4 0%, #E8A598 50%, #C97D6B 100%);';
                    if (c.includes('white') || c.includes('silver') || c.includes('platinum')) {
                        return 'background: linear-gradient(135deg, #FFFFFF 0%, #E8E8E8 50%, #BDBDBD 100%);';
                    }
                    return 'background: linear-gradient(135deg, #FFE082 0%, #D4AF37 50%, #B8860B 100%);';
                },
                playActiveVideo() {
                    this.$nextTick(() => {
                        const media = this.currentMedia;
                        if (media && media.type === 'video' && media.isUpload) {
                            const v = this.showLightbox ? this.$refs.lightboxVideo : this.$refs.mainVideo;
                            if (v && v.paused) {
                                try {
                                    const p = v.play();
                                    if (p !== undefined) {
                                        p.then(() => {
                                            this.isVideoPlaying = true;
                                        }).catch(() => {
                                            if (v && v.paused) {
                                                v.muted = true;
                                                v.play().then(() => {
                                                    this.isVideoPlaying = true;
                                                }).catch(() => {});
                                            }
                                        });
                                    }
                                } catch(e) {}
                            }
                        }
                    });
                },
                nextMedia() {
                    if (this.mediaItems.length > 0) {
                        this.pauseAllVideos();
                        this.activeMedia = (this.activeMedia + 1) % this.mediaItems.length;
                        this.resetLightboxZoom();
                        this.playActiveVideo();
                        this.scrollActiveThumbIntoView();
                    }
                },
                prevMedia() {
                    if (this.mediaItems.length > 0) {
                        this.pauseAllVideos();
                        this.activeMedia = (this.activeMedia - 1 + this.mediaItems.length) % this.mediaItems.length;
                        this.resetLightboxZoom();
                        this.playActiveVideo();
                        this.scrollActiveThumbIntoView();
                    }
                },
                selectColor(color) {
                    if (!color) return;
                    this.selectedColor = color;
                    this.pauseAllVideos();
                    this.activeMedia = 0;
                    this.resetLightboxZoom();
                    this.playActiveVideo();
                    this.scrollActiveThumbIntoView();
                    this.syncUrlWithState();
                },
                selectMedia(idx) {
                    if (this.activeMedia !== idx) {
                        this.pauseAllVideos();
                        this.activeMedia = idx;
                        this.resetLightboxZoom();
                        this.playActiveVideo();
                        this.scrollActiveThumbIntoView();
                    } else if (this.currentMedia && this.currentMedia.type === 'video' && this.currentMedia.isUpload) {
                        const v = this.showLightbox ? this.$refs.lightboxVideo : this.$refs.mainVideo;
                        if (v) {
                            if (v.paused) {
                                v.play().catch(() => {});
                                this.isVideoPlaying = true;
                            } else {
                                v.pause();
                                this.isVideoPlaying = false;
                            }
                        }
                    }
                },

                // Backward compatibility aliases
                get activeImage() { return this.activeMedia; },
                get productImages() { return this.mediaItems.map(m => m.src); },
                selectImage(idx) { this.selectMedia(idx); },
                nextImage() { this.nextMedia(); },
                prevImage() { this.prevMedia(); }
            };
        }
    </script>
    <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 pt-2 sm:pt-4 pb-16 sm:pb-24 w-full min-w-0"
         x-data="productDetail({
            mediaItems: @js($mediaList),
            sizeVariants: @js($product->attributes['size_variants'] ?? []),
            basePrice: {{ (float) $product->effective_price }},
            regularPrice: {{ (float) $product->price }},
            commonCutPrice: {{ (float) ($product->jewellery_attributes['common_cut_price'] ?? 0) }},
            pricingMode: @js($product->attributes['pricing_mode'] ?? 'manual'),
            baseGoldWeight: {{ (float) $product->base_gold_weight }},
            makingChargePerGram: {{ (float) $product->making_charge_per_gram }},
            diamondStoneValue: {{ (float) $product->diamond_stone_value }},
            profitMarginPercent: {{ (float) ($product->jewellery_attributes['profit_margin_percent'] ?? 0) }},
            rate24kt: {{ (float) \App\Services\BullionPricingService::getRates()['rate_24kt'] }},
            bullionRates: @js(\App\Services\BullionPricingService::getRates()),
            sizeLabel: @js($product->size_label),
            selectedSize: @js(request('size') ?: (!empty($product->attributes['size_variants']) ? ($product->attributes['size_variants'][0]['size'] ?? '') : (!empty($product->attributes['sizes']) ? $product->attributes['sizes'][0] : ''))),
            goldPurities: @js($product->gold_purities),
            selectedPurity: @js((request('purity') && in_array(request('purity'), $product->gold_purities)) ? request('purity') : (!empty($product->gold_purities) ? $product->gold_purities[0] : '')),
            purityPrices: @js($product->attributes['purity_prices'] ?? null),
            purityMrp: @js($product->attributes['purity_mrp'] ?? null),
            metalColors: @js($product->metal_colors),
            selectedColor: @js($firstColor),
            colorImages: @js($product->color_images ?: []),
            productTitle: @js($product->name),
            productSku: @js($product->sku ?? ''),
            whatsappNumber: @js(preg_replace('/[^0-9]/', '', \App\Models\SiteSetting::get('contact_whatsapp') ?: \App\Models\SiteSetting::get('contact_phone', '919876543210')))
         })">

        {{-- ── FULL-SCREEN LIGHTBOX MODAL WITH CUT-FREE ZOOM & PAN OR VIDEO CINEMA ── --}}
        <template x-if="showLightbox">
            <div x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-98"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-98"
                 @keydown.escape.window="closeLightbox()"
                 @keydown.arrow-left.window="prevMedia()"
                 @keydown.arrow-right.window="nextMedia()"
                 @keydown.equal.window="zoomIn()"
                 @keydown.minus.window="zoomOut()"
                 @keydown.digit0.window="resetLightboxZoom()"
                 class="fixed inset-0 z-[99999] bg-[#FAF8F5]/96 backdrop-blur-2xl flex flex-col justify-between p-3 sm:p-6 select-none overflow-hidden overscroll-none touch-none text-[#1C1917]"
                 @wheel.prevent="handleWheel($event)"
                 @touchmove="if (lightboxZoom <= 1 || !$event.target.closest('[x-ref=\'lightboxImg\']')) $event.preventDefault()">
                
                {{-- TOP BAR: Indicator/Zoom Controls (Left) & Close Button (Right) --}}
                <div class="w-full flex items-center justify-between z-50 shrink-0 min-h-[48px] px-1 sm:px-3">
                    {{-- Left: Badge & Image Zoom Controls --}}
                    {{-- Left: Image Zoom Controls (if media is image) --}}
                    <div class="flex items-center gap-2 sm:gap-3">
                        <template x-if="currentMedia && currentMedia.type === 'image'">
                            <div class="flex items-center gap-1.5 rounded-full px-3 py-1.5 shadow-sm bg-white border border-[#DFD9CE] text-[#1C1917]">
                                <span class="text-[11px] font-bold tracking-wider px-1 text-[#1C1917]" x-text="(activeMedia + 1) + ' / ' + mediaItems.length"></span>
                                <div class="h-4 w-px bg-[#DFD9CE]"></div>
                                <button type="button" @click.stop="zoomOut()" :disabled="lightboxZoom <= 1" class="w-7 h-7 rounded-full flex items-center justify-center hover:bg-[#F2ECE4] disabled:opacity-30 disabled:cursor-not-allowed transition-all cursor-pointer text-[#1C1917]" title="Zoom Out (-)">
                                    <svg class="w-3.5 h-3.5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                                </button>
                                <span class="text-[11px] font-mono font-bold px-1 text-[#1C1917] min-w-[38px] text-center" x-text="Math.round(lightboxZoom * 100) + '%'"></span>
                                <button type="button" @click.stop="zoomIn()" :disabled="lightboxZoom >= 3" class="w-7 h-7 rounded-full flex items-center justify-center hover:bg-[#F2ECE4] disabled:opacity-30 disabled:cursor-not-allowed transition-all cursor-pointer text-[#1C1917]" title="Zoom In (+)">
                                    <svg class="w-3.5 h-3.5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                </button>
                                <button type="button" @click.stop="resetLightboxZoom()" x-show="lightboxZoom > 1" class="w-7 h-7 rounded-full flex items-center justify-center hover:bg-[#F2ECE4] transition-all cursor-pointer text-[10.5px] font-bold text-[#A87432]" title="Reset Zoom">
                                    1:1
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Right: High-Contrast Bold Close Button (✕) --}}
                    <button type="button" @click.stop="closeLightbox()"
                            class="w-11 h-11 sm:w-12 sm:h-12 rounded-full flex items-center justify-center transition-all duration-200 cursor-pointer shadow-md shrink-0 group border border-[#DFD9CE] bg-white text-[#1C1917] hover:bg-[#1C1917] hover:text-white hover:border-[#1C1917] hover:scale-105 active:scale-95"
                            title="Close (Esc)"
                            aria-label="Close lightbox">
                        <svg class="w-5 h-5 stroke-[2.5] text-current group-hover:rotate-90 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- MIDDLE CANVAS: Zoomable Image OR Full Video Player --}}
                <div class="relative flex-1 w-full flex items-center justify-center overflow-hidden my-auto p-2"
                     @click.self="closeLightbox()">

                    {{-- Previous Arrow --}}
                    <template x-if="mediaItems.length > 1">
                        <button type="button" @click.stop="prevMedia()"
                                class="absolute left-2 sm:left-6 top-1/2 -translate-y-1/2 text-[#1C1917] bg-white hover:bg-[#1C1917] hover:text-white border border-[#DFD9CE] w-11 h-11 sm:w-12 sm:h-12 rounded-full flex items-center justify-center transition-all cursor-pointer shadow-lg z-40 hover:scale-110 active:scale-95"
                                aria-label="Previous media">
                            <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                    </template>

                    {{-- Next Arrow --}}
                    <template x-if="mediaItems.length > 1">
                        <button type="button" @click.stop="nextMedia()"
                                class="absolute right-2 sm:right-6 top-1/2 -translate-y-1/2 text-[#1C1917] bg-white hover:bg-[#1C1917] hover:text-white border border-[#DFD9CE] w-11 h-11 sm:w-12 sm:h-12 rounded-full flex items-center justify-center transition-all cursor-pointer shadow-lg z-40 hover:scale-110 active:scale-95"
                                aria-label="Next media">
                            <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </template>

                    {{-- IMAGE Container (when active item is image) --}}
                    <template x-if="currentMedia && currentMedia.type === 'image'">
                        <div class="relative flex items-center justify-center select-none max-w-full max-h-full"
                             @mousedown="startDrag($event)"
                             @mousemove="onDrag($event)"
                             @mouseup="stopDrag()"
                             @mouseleave="stopDrag()"
                             @touchstart="startDrag($event)"
                             @touchmove="onDrag($event)"
                             @touchend="stopDrag()"
                             @dblclick.stop="toggleLightboxZoom()">
                            <img x-ref="lightboxImg"
                                 :src="currentMedia.src"
                                 alt="{{ $product->name }}"
                                 draggable="false"
                                 class="max-w-[88vw] max-h-[74vh] object-contain rounded-2xl shadow-xl will-change-transform select-none"
                                 :style="'transform: translate(' + lightboxPanX + 'px, ' + lightboxPanY + 'px) scale(' + lightboxZoom + '); transform-origin: center center; transition: ' + (isDragging ? 'none' : 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)') + '; cursor: ' + (lightboxZoom > 1 ? (isDragging ? 'grabbing' : 'grab') : 'zoom-in') + ';'">
                        </div>
                    </template>

                    {{-- VIDEO Container: HTML5 direct video (Cinema Large) --}}
                    <template x-if="currentMedia && currentMedia.type === 'video' && !currentMedia.isYoutube && !currentMedia.isVimeo">
                        <div class="relative w-[94vw] max-w-[1360px] aspect-video max-h-[82vh] flex items-center justify-center bg-black rounded-2xl overflow-hidden shadow-2xl border border-black/10">
                            <video x-ref="lightboxVideo"
                                   controls
                                   playsinline
                                   preload="auto"
                                   :src="currentMedia.src"
                                   :poster="currentMedia.thumb"
                                   @play="isVideoPlaying = true"
                                   @pause="isVideoPlaying = false"
                                   @ended="isVideoPlaying = false"
                                   class="w-full h-full object-contain bg-black rounded-2xl">
                                Your browser does not support HTML5 video.
                            </video>
                        </div>
                    </template>

                    {{-- VIDEO Container: YouTube / Vimeo (Cinema Large) --}}
                    <template x-if="currentMedia && currentMedia.type === 'video' && (currentMedia.isYoutube || currentMedia.isVimeo)">
                        <div class="relative w-[94vw] max-w-[1360px] aspect-video max-h-[82vh] flex items-center justify-center bg-black rounded-2xl overflow-hidden shadow-2xl border border-black/10">
                            <iframe :src="currentMedia.embedUrl"
                                    title="{{ $product->name }} Video"
                                    class="w-full h-full border-0 rounded-2xl"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    referrerpolicy="strict-origin-when-cross-origin"
                                    allowfullscreen>
                            </iframe>
                        </div>
                    </template>
                </div>

                {{-- Lightbox Thumbnails Navigation (Images + Video with Play badge) --}}
                <template x-if="mediaItems.length > 1">
                    <div @click.stop="" class="flex items-center justify-center gap-2 sm:gap-3 pt-2 pb-1 z-50 cursor-default max-w-full overflow-x-auto no-scrollbar px-4 shrink-0">
                        <template x-for="(item, idx) in mediaItems" :key="item.src || ('lightbox_' + idx)">
                            <button type="button" @click="selectMedia(idx)"
                                    class="relative shrink-0 w-12 h-12 sm:w-14 sm:h-14 rounded-xl overflow-hidden border-2 transition-all cursor-pointer p-0.5 bg-white shadow-xs"
                                    :class="activeMedia === idx ? 'border-[#1C1917] ring-2 ring-[#1C1917]/25 scale-105 shadow-md opacity-100' : 'border-[#DFD9CE] opacity-70 hover:opacity-100 hover:border-[#1C1917]/50'">
                                {{-- Uploaded Video: Render actual video first frame --}}
                                <template x-if="item.type === 'video' && item.isUpload">
                                    <video :src="item.src + '#t=0.5'" preload="metadata" muted playsinline tabindex="-1" aria-hidden="true" x-init="$el.muted = true; $el.volume = 0; $el.pause()" class="w-full h-full object-cover rounded-lg pointer-events-none bg-[#141210]"></video>
                                </template>
                                {{-- YouTube / Vimeo: Render official video poster --}}
                                <template x-if="item.type === 'video' && (item.isYoutube || item.isVimeo)">
                                    <img :src="item.thumb" class="w-full h-full object-cover rounded-lg pointer-events-none">
                                </template>
                                {{-- Product Image: Render image --}}
                                <template x-if="item.type === 'image'">
                                    <img :src="item.thumb || item.src" 
                                         draggable="false" 
                                         oncontextmenu="return false;"
                                         class="w-full h-full object-cover rounded-lg pointer-events-none select-none">
                                </template>
                                <template x-if="item.type === 'video'">
                                    <div class="absolute inset-0 bg-black/40 rounded-lg flex items-center justify-center">
                                        <div class="w-5 h-5 rounded-full bg-white text-[#1C1917] flex items-center justify-center shadow-xs">
                                            <svg class="w-2.5 h-2.5 fill-current ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        </div>
                                    </div>
                                </template>
                            </button>
                        </template>
                    </div>
                </template>
            </div>
        </template>

        {{-- ── BREADCRUMB ──────────────────────────────────────── --}}
        <nav class="flex items-center flex-wrap gap-x-2 gap-y-1 text-[10px] uppercase tracking-[0.16em] sm:tracking-[0.2em] text-[#8E877D] mb-6 sm:mb-8 font-medium">
            <a href="{{ url('/') }}" class="hover:text-[#1C1917] transition-colors shrink-0">Home</a>
            <span>·</span>
            <a href="{{ route('shop.index') }}" class="hover:text-[#1C1917] transition-colors shrink-0">Shop</a>
            @if($product->category)
            <span>·</span>
            <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="hover:text-[#1C1917] transition-colors shrink-0">{{ $product->category->name }}</a>
            @endif
            <span>·</span>
            <span class="text-[#1C1917] font-semibold truncate max-w-[180px] sm:max-w-none">{{ $product->name }}</span>
        </nav>

        {{-- ── MAIN PRODUCT LAYOUT ─────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 sm:gap-12 lg:gap-16 xl:gap-20 mb-16 sm:mb-20">

            {{-- LEFT: Product Gallery (Images & Video Showcase) --}}
            <div class="space-y-3.5 sm:space-y-4">
                {{-- Main Gallery Stage --}}
                <div class="relative overflow-hidden aspect-square rounded-[1.75rem] sm:rounded-[2rem] bg-[#F5F2EB] border border-[#DFD9CE]/60 shadow-2xs select-none group">

                    @if(!empty($mediaList))
                        {{-- Image Viewers --}}
                        <template x-if="currentMedia && currentMedia.type === 'image'">
                            <div class="w-full h-full cursor-zoom-in relative" @click="openLightbox()">
                                <img :src="currentMedia.src"
                                     alt="{{ $product->name }}"
                                     draggable="false"
                                     oncontextmenu="return false;"
                                     class="w-full h-full object-cover transition-opacity duration-300 pointer-events-none select-none">
                                {{-- Click to zoom hint on desktop --}}
                                <div class="absolute bottom-3.5 right-3.5 hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/95 backdrop-blur-md border border-[#DFD9CE] text-[11px] text-[#1C1917] font-semibold opacity-90 group-hover:opacity-100 group-hover:scale-105 transition-all duration-200 pointer-events-none shadow-sm">
                                    <svg class="w-3.5 h-3.5 stroke-[2.2] text-[#A87432]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                    <span>Click to zoom</span>
                                </div>
                            </div>
                        </template>

                        {{-- Video Viewer: HTML5 direct video --}}
                        <template x-if="currentMedia && currentMedia.type === 'video' && !currentMedia.isYoutube && !currentMedia.isVimeo">
                            <div class="w-full h-full relative bg-[#141210] flex items-center justify-center">
                                <video x-ref="mainVideo"
                                       controls
                                       playsinline
                                       preload="metadata"
                                       :poster="currentMedia.thumb"
                                       :src="currentMedia.src"
                                       @play="isVideoPlaying = true"
                                       @pause="isVideoPlaying = false"
                                       @ended="isVideoPlaying = false"
                                       class="w-full h-full object-contain">
                                    Your browser does not support HTML5 video.
                                </video>
                                {{-- Fullscreen cinema lightbox button --}}
                                <button type="button" @click.stop="openLightbox()"
                                        class="absolute top-3 right-3 p-2 rounded-full bg-black/60 hover:bg-black/90 text-white backdrop-blur-md transition-all shadow-md z-20 cursor-pointer"
                                        title="View full screen cinema">
                                    <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0 0l-5-5m-6 16v4m0 0H4m4 0l-5-5m16 5h-4m4 0v-4m0 0l-5 5"/>
                                    </svg>
                                </button>
                            </div>
                        </template>

                        {{-- Video Viewer: YouTube / Vimeo Embed --}}
                        <template x-if="currentMedia && currentMedia.type === 'video' && (currentMedia.isYoutube || currentMedia.isVimeo)">
                            <div class="w-full h-full relative bg-black flex items-center justify-center">
                                <iframe :src="currentMedia.embedUrl"
                                        title="{{ $product->name }} Video"
                                        class="w-full h-full aspect-square border-0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        referrerpolicy="strict-origin-when-cross-origin"
                                        allowfullscreen>
                                </iframe>
                                {{-- Fullscreen cinema lightbox button --}}
                                <button type="button" @click.stop="openLightbox()"
                                        class="absolute top-3 right-3 p-2 rounded-full bg-black/60 hover:bg-black/90 text-white backdrop-blur-md transition-all shadow-md z-20 cursor-pointer"
                                        title="View full screen cinema">
                                    <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0 0l-5-5m-6 16v4m0 0H4m4 0l-5-5m16 5h-4m4 0v-4m0 0l-5 5"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    @else
                        <div class="w-full h-full flex items-center justify-center text-[#8E877D] text-sm">
                            No media available
                        </div>
                    @endif

                    {{-- Navigation Arrows (Visible on mobile, hover-revealed on desktop) --}}
                    @if(count($mediaList) > 1)
                        <button type="button" @click.stop="prevMedia()"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/85 backdrop-blur-md border border-[#DFD9CE]/80 text-[#1C1917] flex items-center justify-center shadow-sm opacity-90 sm:opacity-0 group-hover:!opacity-100 transition-all duration-200 cursor-pointer hover:bg-white z-10"
                                aria-label="Previous media">
                            <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" @click.stop="nextMedia()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/85 backdrop-blur-md border border-[#DFD9CE]/80 text-[#1C1917] flex items-center justify-center shadow-sm opacity-90 sm:opacity-0 group-hover:!opacity-100 transition-all duration-200 cursor-pointer hover:bg-white z-10"
                                aria-label="Next media">
                            <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        {{-- Dot Indicators for mobile --}}
                        <div class="absolute bottom-3.5 left-1/2 -translate-x-1/2 flex items-center gap-1.5 sm:hidden z-10 pointer-events-none">
                            <template x-for="(item, idx) in mediaItems" :key="item.src || ('dot_' + idx)">
                                <span class="h-1.5 rounded-full transition-all duration-300"
                                      :class="activeMedia === idx ? 'bg-[#1C1917] w-4' : 'bg-[#1C1917]/35 w-1.5'"></span>
                            </template>
                        </div>
                    @endif
                </div>

                {{-- Media Thumbnails: Images & Video Showcase --}}
                @if(count($mediaList) > 1)
                    <div class="relative w-full group/thumbstrip py-1"
                         @mouseenter="updateThumbScrollState()">
                        
                        {{-- Left Arrow Button --}}
                        <button type="button"
                                x-show="canScrollThumbLeft"
                                x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-x-2 scale-90"
                                x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-x-2 scale-90"
                                @click.stop="scrollThumbLeft()"
                                class="absolute -left-2 sm:-left-3.5 top-1/2 -translate-y-1/2 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/95 hover:bg-[#1C1917] text-[#1C1917] hover:text-white border border-[#DFD9CE] shadow-md flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95 z-30 cursor-pointer"
                                aria-label="Previous thumbnails">
                            <svg class="w-4 h-4 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>

                        {{-- Left Gradient Edge Shadow (Soft fade hint) --}}
                        <div x-show="canScrollThumbLeft"
                             x-cloak
                             x-transition.opacity.duration.200ms
                             class="pointer-events-none absolute left-0 top-0 bottom-0 w-8 sm:w-10 bg-gradient-to-r from-[#FAF8F5] via-[#FAF8F5]/80 to-transparent z-10 rounded-l-xl"></div>

                        {{-- Thumbnails Scroll Track --}}
                        <div x-ref="thumbContainer"
                             @scroll.passive="updateThumbScrollState()"
                             class="flex items-center gap-2.5 sm:gap-3 overflow-x-auto no-scrollbar scroll-smooth py-1 px-1">
                            <template x-for="(item, idx) in mediaItems" :key="item.src || ('thumb_' + idx)">
                                <button type="button" 
                                        :data-thumb-idx="idx"
                                        @click="selectMedia(idx)"
                                        class="shrink-0 w-16 h-16 sm:w-20 sm:h-20 aspect-square overflow-hidden rounded-xl sm:rounded-2xl bg-[#F5F2EB] border-2 transition-all duration-200 cursor-pointer relative p-0.5 select-none"
                                        :class="activeMedia === idx ? 'border-[#1C1917] ring-2 ring-[#1C1917]/25 scale-105 opacity-100 shadow-xs' : 'border-[#DFD9CE]/70 opacity-70 hover:opacity-100 hover:border-[#1C1917]/50'">
                                    
                                    {{-- 1. If uploaded MP4 video, display actual video frame --}}
                                    <template x-if="item.type === 'video' && item.isUpload">
                                        <div class="w-full h-full relative overflow-hidden rounded-lg sm:rounded-xl bg-[#141210]">
                                            <video :src="item.src + '#t=0.5'"
                                                   preload="metadata"
                                                   muted
                                                   playsinline
                                                   tabindex="-1"
                                                   aria-hidden="true"
                                                   x-init="$el.muted = true; $el.volume = 0; $el.pause()"
                                                   class="w-full h-full object-cover pointer-events-none bg-[#141210]">
                                            </video>
                                        </div>
                                    </template>

                                    {{-- 2. If YouTube / Vimeo, display official API thumbnail --}}
                                    <template x-if="item.type === 'video' && (item.isYoutube || item.isVimeo)">
                                        <img :src="item.thumb" :alt="'Video ' + (idx + 1)" class="w-full h-full object-cover rounded-lg sm:rounded-xl pointer-events-none">
                                    </template>

                                    {{-- 3. If standard product image, display photo (Drag & Steal Protected) --}}
                                    <template x-if="item.type === 'image'">
                                        <img :src="item.thumb || item.src" 
                                             :alt="'Media ' + (idx + 1)" 
                                             draggable="false"
                                             oncontextmenu="return false;"
                                             class="w-full h-full object-cover rounded-lg sm:rounded-xl pointer-events-none select-none">
                                    </template>

                                    {{-- Video Badge & Overlay (if video item) --}}
                                    <template x-if="item.type === 'video'">
                                        <div class="absolute inset-0 bg-black/40 rounded-lg sm:rounded-xl flex flex-col items-center justify-center text-white transition-colors hover:bg-black/25">
                                            {{-- Center Play Disc --}}
                                            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-white text-[#1C1917] flex items-center justify-center shadow-md mb-0.5">
                                                <svg class="w-3 h-3 fill-current ml-0.5" viewBox="0 0 24 24">
                                                    <path d="M8 5v14l11-7z"/>
                                                </svg>
                                            </div>
                                            {{-- Video Pill Indicator --}}
                                            <span class="bg-[#1C1917]/95 text-white text-[8px] sm:text-[8.5px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded-xs shadow-xs">
                                                VIDEO
                                            </span>
                                        </div>
                                    </template>
                                </button>
                            </template>
                        </div>

                        {{-- Right Gradient Edge Shadow (Soft fade hint) --}}
                        <div x-show="canScrollThumbRight"
                             x-cloak
                             x-transition.opacity.duration.200ms
                             class="pointer-events-none absolute right-0 top-0 bottom-0 w-8 sm:w-10 bg-gradient-to-l from-[#FAF8F5] via-[#FAF8F5]/80 to-transparent z-10 rounded-r-xl"></div>

                        {{-- Right Arrow Button --}}
                        <button type="button"
                                x-show="canScrollThumbRight"
                                x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-x-2 scale-90"
                                x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-x-2 scale-90"
                                @click.stop="scrollThumbRight()"
                                class="absolute -right-2 sm:-right-3.5 top-1/2 -translate-y-1/2 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/95 hover:bg-[#1C1917] text-[#1C1917] hover:text-white border border-[#DFD9CE] shadow-md flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95 z-30 cursor-pointer"
                                aria-label="Next thumbnails">
                            <svg class="w-4 h-4 stroke-[2.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                @endif
            </div>

            {{-- RIGHT: Product Info & Actions (Restored to Exact Atelier Luxury Design Look) --}}
            <div class="space-y-6">
                {{-- Category & Inventory Status Pill Tag --}}
                <div>
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <p class="text-[10px] uppercase tracking-[0.28em] font-medium text-[#8E877D]">
                            {{ $product->category?->name ?? 'FINE JEWELLERY' }}
                        </p>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[9px] uppercase tracking-[0.18em] font-medium bg-[#F5F2EB] text-[#78716C] border border-[#DFD9CE]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#A8A29E]"></span>
                            {{ $product->inventory_type === 'MADE_TO_ORDER' ? 'MADE TO ORDER' : 'READY TO SHIP' }}
                        </span>
                    </div>
                    <h1 class="font-editorial text-3xl sm:text-4xl lg:text-5xl text-[#1C1917] font-light leading-[1.12] sm:leading-[1.08] tracking-tight">
                        {{ $product->name }}
                    </h1>
                </div>

                {{-- Price & Review Rating (Interlinked to Customer Reviews) --}}
                @php
                    $revCount = $product->reviews_count ?? ($product->reviews ? $product->reviews->count() : 0);
                    $avgRating = $product->average_rating ?? 5.0;
                    $fullStars = floor($avgRating);
                    $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                @endphp
                <div class="space-y-3 pt-1" x-data="{ openBreakdown: false }">
                    <div class="flex items-baseline justify-between gap-4 flex-wrap">
                        <div class="flex items-baseline gap-2.5 sm:gap-3 flex-wrap">
                            <span class="font-sans font-medium text-2xl sm:text-3.5xl text-[#1C1917] tracking-tight">
                                ₹ <span x-text="formatPrice(currentPrice)">{{ number_format($product->effective_price) }}</span>
                            </span>
                            <template x-if="currentMrp && currentMrp > currentPrice">
                                <s class="text-sm sm:text-base text-[#8E877D] font-light font-sans tracking-tight">₹ <span x-text="formatPrice(currentMrp)">{{ number_format($product->price) }}</span></s>
                            </template>
                            <span class="text-[11px] text-[#8E877D] font-light tracking-wide">(Inclusive of all taxes)</span>
                        </div>

                        <a href="#customer-reviews" onclick="document.getElementById('customer-reviews')?.scrollIntoView({ behavior: 'smooth' });"
                           class="text-[10px] uppercase tracking-[0.16em] sm:tracking-[0.18em] text-[#8E877D] hover:text-[#1C1917] font-light flex items-center gap-1.5 transition-colors cursor-pointer shrink-0">
                            <span class="text-[#C8A96E] text-xs tracking-wider flex items-center">
                                {!! str_repeat('★', $fullStars) !!}{!! $hasHalfStar ? '★' : '' !!}{!! str_repeat('☆', $emptyStars) !!}
                            </span>
                            <span>· {{ $revCount }} {{ Str::plural('REVIEW', $revCount) }}</span>
                        </a>
                    </div>

                </div>


                {{-- 1. Gold Purity Selector (Multiple purities show pills; single purity shows label; empty hides) --}}
                <template x-if="goldPurities && goldPurities.length > 1">
                    <div class="space-y-2">
                        <p class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#1C1917]">
                            GOLD PURITY <span class="font-normal text-[#8E877D] tracking-normal ml-1" x-text="selectedPurity ? '· ' + selectedPurity : ''"></span>
                        </p>
                        <div class="flex items-center gap-2 flex-wrap">
                            <template x-for="purity in goldPurities" :key="purity">
                                <button type="button" @click="selectedPurity = purity; if (typeof syncUrlWithState === 'function') syncUrlWithState();"
                                        :class="selectedPurity === purity ? 'bg-[#1C1917] text-white border-[#1C1917]' : 'bg-transparent text-[#1C1917] border-[#EBE6DD] hover:border-[#1C1917]'"
                                        class="px-4 sm:px-5 py-2 rounded-full border text-[9px] sm:text-[9.5px] uppercase tracking-[0.18em] sm:tracking-[0.2em] font-medium transition-all duration-200 cursor-pointer"
                                        x-text="purity">
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
                <template x-if="goldPurities && goldPurities.length === 1">
                    <p class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#8E877D]">
                        GOLD PURITY · <span class="text-[#1C1917]" x-text="goldPurities[0]"></span>
                    </p>
                </template>

                {{-- 2. Dynamic Size Selector (Necklace Length, Ring Size, etc.) --}}
                <template x-if="sizeVariants && sizeVariants.length > 0">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#1C1917]">
                                <span>SIZE</span>
                                <span class="font-normal text-[#8E877D] tracking-normal ml-1" x-text="selectedSize ? '· ' + selectedSize : ''"></span>
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <template x-for="v in sizeVariants" :key="v.size">
                                <button type="button" @click="selectedSize = v.size; if (typeof syncUrlWithState === 'function') syncUrlWithState();"
                                        :class="selectedSize === v.size ? 'bg-[#1C1917] text-white border-[#1C1917]' : 'bg-transparent text-[#1C1917] border-[#EBE6DD] hover:border-[#1C1917]'"
                                        class="px-4 sm:px-5 py-2 rounded-full border text-[9px] sm:text-[9.5px] uppercase tracking-[0.18em] sm:tracking-[0.2em] font-medium transition-all duration-200 cursor-pointer">
                                    <span x-text="v.size"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- 3. Metal Color Swatches (Multiple colors show swatches; single color shows clean label; empty hides) --}}
                <template x-if="metalColors && metalColors.length > 1">
                    <div class="space-y-2">
                        <p class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#1C1917]">
                            COLOR <span class="font-normal text-[#8E877D] tracking-normal ml-1" x-text="selectedColor ? '· ' + selectedColor : ''"></span>
                        </p>
                        <div class="flex items-center gap-2.5">
                            <template x-for="color in metalColors" :key="color">
                                <button type="button" @click="selectColor(color)"
                                        :title="color"
                                        class="w-7 h-7 rounded-full border-2 transition-all p-0.5 relative cursor-pointer focus:outline-none"
                                        :class="selectedColor === color ? 'border-[#1C1917] ring-2 ring-[#1C1917]/25 scale-110' : 'border-[#DFD9CE] hover:border-[#8E877D]'">
                                    <span class="block w-full h-full rounded-full shadow-inner border border-black/10" :style="getColorStyle(color)"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
                <template x-if="metalColors && metalColors.length === 1">
                    <p class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#8E877D]">
                        COLOR · <span class="text-[#1C1917]" x-text="metalColors[0]"></span>
                    </p>
                </template>

                {{-- 4. Quantity + Add to Bag + Wishlist Row (Original Luxury Pill Aesthetic) --}}
                <div class="space-y-2.5 pt-1" x-data="{ showShareModal: false, copied: false }">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        {{-- Add to Bag Form --}}
                        <form method="POST" action="{{ route('cart.add') }}" class="flex items-center gap-2.5 sm:gap-3 flex-1 min-w-0">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="options[gold_purity]" :value="selectedPurity">
                            <input type="hidden" name="options[metal_color]" :value="selectedColor">
                            <template x-if="selectedSize">
                                <input type="hidden" name="options[size]" :value="selectedSize">
                            </template>

                            {{-- Qty Counter Pill --}}
                            <div class="flex items-center justify-between border border-[#DFD9CE] rounded-full px-3 sm:px-4 bg-white w-24 sm:w-28 h-12 shrink-0">
                                <button type="button" @click="if(qty > 1) qty--"
                                        class="w-6 h-6 flex items-center justify-center text-[#78716C] hover:text-[#1C1917] text-base leading-none font-light cursor-pointer select-none">−</button>
                                <span class="text-xs font-semibold text-[#1C1917]" x-text="qty"></span>
                                <button type="button" @click="qty++"
                                        class="w-6 h-6 flex items-center justify-center text-[#78716C] hover:text-[#1C1917] text-base leading-none font-light cursor-pointer select-none">+</button>
                                <input type="hidden" name="quantity" :value="qty">
                            </div>

                            {{-- Add to Bag Button --}}
                            <button type="submit"
                                    class="flex-1 h-12 bg-[#1C1917] hover:bg-[#2D2825] text-white text-[10px] sm:text-[11px] uppercase tracking-[0.16em] sm:tracking-[0.25em] font-semibold rounded-full transition-all duration-300 shadow-xs cursor-pointer flex items-center justify-center text-center px-2">
                                ADD TO BAG
                            </button>
                        </form>

                        {{-- Independent Wishlist Toggle Form (Matching Original Circular Styling) --}}
                        @php
                            $isWishlisted = false;
                            if (Auth::check()) {
                                $isWishlisted = \App\Models\Wishlist::where('user_id', Auth::id())->where('product_id', $product->id)->exists();
                            } else {
                                $isWishlisted = in_array($product->id, session('guest_wishlist', []));
                            }
                        @endphp
                        <form method="POST" action="{{ route('wishlist.toggle') }}" class="wishlist-toggle-form inline shrink-0" data-product-id="{{ $product->id }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" title="{{ $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}"
                                    class="wishlist-btn w-12 h-12 rounded-full transition-all cursor-pointer shrink-0 flex items-center justify-center {{ $isWishlisted ? 'bg-[#1C1917] text-white shadow-xs' : 'bg-white border border-[#DFD9CE] text-[#1C1917] hover:border-[#1C1917] shadow-2xs' }}"
                                    data-product-id="{{ $product->id }}"
                                    data-wishlisted="{{ $isWishlisted ? 'true' : 'false' }}"
                                    data-style-type="detail-main">
                                <svg class="wishlist-icon w-4 h-4 stroke-[1.75] transition-all duration-200 {{ $isWishlisted ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                </svg>
                            </button>
                        </form>
                    </div>

                    {{-- Buy Now + Share Row (Original Rounded-Full Pill Styling) --}}
                    <div class="flex items-center gap-2.5">
                        <form method="POST" action="{{ route('cart.buy-now') }}" class="flex-1 min-w-0">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" :value="qty">
                            <input type="hidden" name="options[gold_purity]" :value="selectedPurity">
                            <input type="hidden" name="options[metal_color]" :value="selectedColor">
                            <template x-if="selectedSize">
                                <input type="hidden" name="options[size]" :value="selectedSize">
                            </template>
                            <button type="submit"
                                    class="w-full h-12 flex items-center justify-center text-center border border-[#DFD9CE] hover:border-[#1C1917] bg-white text-[#1C1917] text-[10px] sm:text-[11px] uppercase tracking-[0.16em] sm:tracking-[0.25em] font-semibold rounded-full transition-all duration-300 shadow-2xs cursor-pointer">
                                BUY NOW
                            </button>
                        </form>

                        <button type="button"
                                @click="
                                    const sharePayload = {
                                        title: {{ Js::from($product->name) }},
                                        text: {{ Js::from('Discover ' . $product->name . ' — fine certified jewellery.') }},
                                        url: window.location.href
                                    };
                                    if (navigator.share) {
                                        navigator.share(sharePayload).catch(err => {
                                            if (err.name !== 'AbortError') {
                                                showShareModal = true;
                                            }
                                        });
                                    } else {
                                        showShareModal = true;
                                    }
                                "
                                class="h-12 shrink-0 px-5 sm:px-6 flex items-center justify-center border border-[#DFD9CE] hover:border-[#1C1917] bg-white text-[#1C1917] text-[10px] sm:text-[11px] uppercase tracking-[0.16em] sm:tracking-[0.25em] font-semibold rounded-full transition-all duration-300 shadow-2xs cursor-pointer">
                            SHARE
                        </button>
                    </div>

                    {{-- WhatsApp Direct Order & Bulk Wholesale Inquiry (Matching Original Rounded-Full Pill Styling) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                        <a :href="whatsappOrderUrl" target="_blank"
                           class="h-12 flex items-center justify-center gap-2.5 bg-[#25D366] hover:bg-[#1fa951] text-white text-[10px] sm:text-[11px] uppercase tracking-[0.16em] font-semibold rounded-full px-4 transition-all duration-300 shadow-2xs cursor-pointer">
                            <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                            <span>Order via WhatsApp</span>
                        </a>

                        <a :href="whatsappBulkUrl" target="_blank"
                           class="h-12 flex items-center justify-center gap-2 border border-[#DFD9CE] hover:border-[#1C1917] bg-white text-[#1C1917] text-[10px] sm:text-[11px] uppercase tracking-[0.16em] font-semibold rounded-full px-4 transition-all duration-300 shadow-2xs cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#8E877D] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>Bulk / Wholesale Inquiry</span>
                        </a>
                    </div>

                    {{-- SHARE POPUP MODAL --}}
                    <div x-show="showShareModal" x-cloak
                         @keydown.escape.window="showShareModal = false"
                         class="fixed inset-0 z-[99999] bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
                        <div @click.away="showShareModal = false"
                             class="bg-[#FAF8F5] border border-[#E6E1D7] rounded-[2rem] p-6 sm:p-7 max-w-sm w-full space-y-4 shadow-2xl text-left">
                            <div class="flex items-center justify-between pb-3 border-b border-[#E6E1D7]">
                                <div>
                                    <span class="text-[9px] uppercase tracking-[0.2em] font-semibold text-[#8E877D] block">SHARE PIECE</span>
                                    <h3 class="font-editorial text-xl text-[#1C1917] font-normal mt-0.5">Share this <em class="italic">jewellery</em></h3>
                                </div>
                                <button type="button" @click="showShareModal = false" 
                                        class="w-7 h-7 rounded-full border border-[#DFD9CE] flex items-center justify-center text-[#78716C] hover:text-[#1C1917] hover:border-[#1C1917] transition-colors text-sm font-light cursor-pointer">
                                    &times;
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-2.5 pt-1">
                                <a :href="'https://api.whatsapp.com/send?text=' + encodeURIComponent('Check out ' + {{ Js::from($product->name) }} + ': ' + window.location.href)"
                                   target="_blank" 
                                   class="flex items-center justify-center gap-2 bg-white hover:bg-[#F5F2EB] border border-[#DFD9CE] text-[#1C1917] text-xs font-semibold py-3 rounded-xl transition-all shadow-2xs">
                                    <svg class="w-4 h-4 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.971.53 1.83.81 2.805.81h.001c3.182 0 5.768-2.587 5.769-5.766.001-3.182-2.585-5.796-5.77-5.796zm0 10.427c-.881 0-1.683-.243-2.378-.669l-.17-.104-1.58.414.422-1.54-.112-.178c-.469-.747-.717-1.464-.716-2.584.001-2.433 1.979-4.411 4.414-4.411 2.435 0 4.413 1.979 4.413 4.412 0 2.434-1.977 4.66-4.413 4.66z"/></svg>
                                    <span>WhatsApp</span>
                                </a>

                                <a :href="'https://twitter.com/intent/tweet?text=' + encodeURIComponent({{ Js::from($product->name) }}) + '&url=' + encodeURIComponent(window.location.href)"
                                   target="_blank" 
                                   class="flex items-center justify-center gap-2 bg-white hover:bg-[#F5F2EB] border border-[#DFD9CE] text-[#1C1917] text-xs font-semibold py-3 rounded-xl transition-all shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-[#1C1917]" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    <span>Twitter / X</span>
                                </a>

                                <button type="button" 
                                        @click="
                                            navigator.clipboard.writeText(window.location.href);
                                            copied = true;
                                            setTimeout(() => { copied = false; showShareModal = false; }, 1400);
                                        "
                                        class="col-span-2 flex items-center justify-center gap-2 bg-[#1A1615] hover:bg-[#2C2724] text-white text-xs font-semibold py-3 rounded-xl transition-all shadow-2xs cursor-pointer">
                                    <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <svg x-show="copied" class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span x-text="copied ? 'Copied!' : 'Copy Link'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Atelier Trust Highlights / E-Commerce Badges (Managed globally via Admin Panel → Site Settings) --}}
                @php
                    $b1Icon  = \App\Models\SiteSetting::get('global_badge_1_icon', 'diamond');
                    $b1Title = \App\Models\SiteSetting::get('global_badge_1_title', '100% Certified');
                    $b1Sub   = \App\Models\SiteSetting::get('global_badge_1_subtitle', 'NATURAL DIAMONDS');

                    $b2Icon  = \App\Models\SiteSetting::get('global_badge_2_icon', 'hallmark');
                    $defaultB2Title = $product->inventory_type === 'MADE_TO_ORDER' ? 'Custom Made' : 'BIS Hallmarked';
                    $b2Title = \App\Models\SiteSetting::get('global_badge_2_title', $defaultB2Title);
                    $b2Sub   = \App\Models\SiteSetting::get('global_badge_2_subtitle', '14K - 22K GOLD');

                    $b3Icon  = \App\Models\SiteSetting::get('global_badge_3_icon', 'globe');
                    $b3Title = \App\Models\SiteSetting::get('global_badge_3_title', 'Free Insured');
                    $b3Sub   = \App\Models\SiteSetting::get('global_badge_3_subtitle', 'WORLDWIDE DISPATCH');

                    $renderBadgeIcon = function($iconKey) {
                        $iconClass = "w-4 h-4 sm:w-5 sm:h-5 mx-auto mb-1.5 text-[#9C8262] transition-transform duration-300 group-hover:scale-110";
                        switch ($iconKey) {
                            case 'diamond':
                                return '<svg class="'.$iconClass.'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 3.75h12l4 5.25L12 21 2 9l4-5.25z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 9h19M8 3.75l2 5.25-2 11M16 3.75l-2 5.25 2 11M12 9v12"/></svg>';
                            case 'hallmark':
                                return '<svg class="'.$iconClass.'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><circle cx="12" cy="9" r="6" stroke-linecap="round" stroke-linejoin="round"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.5L7 21l5-2.5 5 2.5-2-6.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M10 9l1.5 1.5L14 7.5"/></svg>';
                            case 'globe':
                                return '<svg class="'.$iconClass.'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><circle cx="12" cy="12" r="9.5" stroke-linecap="round" stroke-linejoin="round"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12h19M12 2.5a15.3 15.3 0 014 9.5 15.3 15.3 0 01-4 9.5 15.3 15.3 0 01-4-9.5 15.3 15.3 0 014-9.5z"/></svg>';
                            case 'shield':
                                return '<svg class="'.$iconClass.'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3s7 2.5 7 8c0 5-3.5 8-7 10-3.5-2-7-5-7-10 0-5.5 7-8 7-8z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.5 11.5l2 2 3.5-3.5"/></svg>';
                            case 'sparkles':
                                return '<svg class="'.$iconClass.'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v4m0 12v4M2 12h4m12 0h4m-3.5-6.5l-2.8 2.8m-7.4 7.4l-2.8 2.8m0-13l2.8 2.8m7.4 7.4l2.8 2.8"/></svg>';
                            case 'truck':
                                return '<svg class="'.$iconClass.'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2 5h12v11H2V5zm12 4h4l3 3.5V16h-7V9z"/><circle cx="6" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>';
                            case 'certificate':
                                return '<svg class="'.$iconClass.'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h4m-6-8h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>';
                            default:
                                return '<svg class="'.$iconClass.'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><circle cx="12" cy="12" r="9" stroke-linecap="round" stroke-linejoin="round"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>';
                        }
                    };
                @endphp
                <div class="grid grid-cols-3 gap-2 sm:gap-3.5 pt-1">
                    <div class="group text-center glass border border-[#DFD9CE]/80 rounded-[1.25rem] sm:rounded-[1.5rem] py-3 px-1.5 sm:py-3.5 sm:px-2 shadow-2xs overflow-hidden transition-all duration-300 hover:border-[#C4AB82] hover:shadow-xs">
                        {!! $renderBadgeIcon($b1Icon) !!}
                        <p class="font-editorial text-sm sm:text-base lg:text-lg font-normal text-[#1C1917] leading-none truncate">{{ $b1Title }}</p>
                        <p class="text-[7px] sm:text-[9px] uppercase tracking-[0.08em] sm:tracking-[0.1em] text-[#8E877D] font-medium mt-1 truncate">{{ $b1Sub }}</p>
                    </div>
                    <div class="group text-center glass border border-[#DFD9CE]/80 rounded-[1.25rem] sm:rounded-[1.5rem] py-3 px-1.5 sm:py-3.5 sm:px-2 shadow-2xs overflow-hidden transition-all duration-300 hover:border-[#C4AB82] hover:shadow-xs">
                        {!! $renderBadgeIcon($b2Icon) !!}
                        <p class="font-editorial text-sm sm:text-base lg:text-lg font-normal text-[#1C1917] leading-none truncate">{{ $b2Title }}</p>
                        <p class="text-[7px] sm:text-[9px] uppercase tracking-[0.08em] sm:tracking-[0.1em] text-[#8E877D] font-medium mt-1 truncate">{{ $b2Sub }}</p>
                    </div>
                    <div class="group text-center glass border border-[#DFD9CE]/80 rounded-[1.25rem] sm:rounded-[1.5rem] py-3 px-1.5 sm:py-3.5 sm:px-2 shadow-2xs overflow-hidden transition-all duration-300 hover:border-[#C4AB82] hover:shadow-xs">
                        {!! $renderBadgeIcon($b3Icon) !!}
                        <p class="font-editorial text-sm sm:text-base lg:text-lg font-normal text-[#1C1917] leading-none truncate">{{ $b3Title }}</p>
                        <p class="text-[7px] sm:text-[9px] uppercase tracking-[0.08em] sm:tracking-[0.1em] text-[#8E877D] font-medium mt-1 truncate">{{ $b3Sub }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── DETAIL TABS & POLICY SECTION ── --}}
        <div class="pt-8 sm:pt-12 mb-16 sm:mb-20" x-data="{ activeTab: 'description' }">
            <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap pb-2 sm:pb-4 mb-6 sm:mb-8">
                <button type="button" @click="activeTab = 'description'"
                        :class="activeTab === 'description' ? 'bg-[#1C1917] text-white border-[#1C1917]' : 'bg-transparent text-[#78716C] border-[#DFD9CE] hover:border-[#1C1917] hover:text-[#1C1917]'"
                        class="px-4 sm:px-5 py-2 sm:py-2.5 rounded-full border text-[9px] sm:text-[9.5px] uppercase tracking-[0.18em] sm:tracking-[0.2em] font-medium transition-all duration-300 cursor-pointer shadow-2xs">
                    PRODUCT DETAILS
                </button>
                <button type="button" @click="activeTab = 'shipping'"
                        :class="activeTab === 'shipping' ? 'bg-[#1C1917] text-white border-[#1C1917]' : 'bg-transparent text-[#78716C] border-[#DFD9CE] hover:border-[#1C1917] hover:text-[#1C1917]'"
                        class="px-4 sm:px-5 py-2 sm:py-2.5 rounded-full border text-[9px] sm:text-[9.5px] uppercase tracking-[0.18em] sm:tracking-[0.2em] font-medium transition-all duration-300 cursor-pointer shadow-2xs">
                    SHIPPING &amp; POLICY
                </button>
            </div>

            {{-- Single Clean Full-Width Content Container --}}
            <div class="glass rounded-[1.5rem] sm:rounded-[2rem] border border-[#DFD9CE] p-4 sm:p-8 lg:p-12 shadow-xs">
                    <div x-show="activeTab === 'description'" class="space-y-8">
                        {{-- Rich Description Paragraphs (Shown on Top) --}}
                        <div class="artisan-rich-text">
                            {!! $product->description ?? 'Master handcrafted fine jewellery featuring certified natural diamonds set in hallmarked gold. Created with enduring luxury and bespoke attention to detail.' !!}
                        </div>

                        {{-- Fine Jewellery Specifications (Clean Structured 2-Column Grid) --}}
                        <div class="mt-8">
                            <h4 class="font-editorial text-2xl sm:text-3xl text-[#1C1917] font-normal tracking-tight mb-4 flex items-center justify-between">
                                <span>Jewellery <em class="italic font-light">Specifications</em></span>
                                <span class="text-[9px] uppercase tracking-[0.2em] text-[#8E877D] font-sans font-medium hidden sm:inline-block">Authentic Fine Jewels</span>
                            </h4>

                            <div class="bg-[#FAF8F5]/80 border border-[#E6E1D7] rounded-2xl overflow-hidden shadow-2xs">
                                @php
                                    $rawVariants = $product->attributes['size_variants'] ?? ($product->size_variants ?? null);
                                    if (is_string($rawVariants)) {
                                        $decoded = json_decode($rawVariants, true);
                                        $rawVariants = is_array($decoded) ? $decoded : [];
                                    }
                                    $rawSizes = $product->attributes['sizes'] ?? ($product->sizes ?? null);
                                    if (is_string($rawSizes)) {
                                        $decoded = json_decode($rawSizes, true);
                                        $rawSizes = is_array($decoded) ? $decoded : [];
                                    }
                                    $rawRingSizes = $product->ring_sizes ?? null;
                                    if (is_string($rawRingSizes)) {
                                        $decoded = json_decode($rawRingSizes, true);
                                        $rawRingSizes = is_array($decoded) ? $decoded : [];
                                    }
                                    $hasSizes = (!empty($rawVariants) && count($rawVariants) > 0)
                                             || (!empty($rawSizes) && count($rawSizes) > 0)
                                             || (!empty($rawRingSizes) && count($rawRingSizes) > 0);

                                    $hasGoldWeight = ($product->base_gold_weight > 0) 
                                        || !empty($product->attributes['gold_weight']) 
                                        || !empty($product->attributes['gold_weight_grams'])
                                        || (!empty($rawVariants) && collect($rawVariants)->contains(fn($v) => !empty($v['weight_grams'])));

                                    $hasPurity = !empty($product->gold_purities) && count($product->gold_purities) > 0;

                                    $allSpecs = [];

                                    if ($hasGoldWeight) {
                                        $allSpecs[] = [
                                            'label' => 'Approx. Gold Weight',
                                            'is_alpine' => true,
                                            'alpine_text' => "Number(currentGoldWeight).toFixed(2) + ' g'",
                                            'static_value' => number_format((float) ($product->base_gold_weight ?: ($product->attributes['gold_weight_grams'] ?? 0)), 2) . ' g',
                                        ];
                                    }

                                    if ($hasPurity) {
                                        $allSpecs[] = [
                                            'label' => 'Gold Purity',
                                            'is_alpine' => true,
                                            'alpine_text' => "selectedPurity",
                                            'static_value' => $product->gold_purities[0] ?? '',
                                        ];
                                    }

                                    // Metal Tone is always present
                                    $allSpecs[] = [
                                        'label' => 'Metal Tone',
                                        'is_alpine' => true,
                                        'alpine_text' => "selectedColor || 'Yellow Gold'",
                                        'static_value' => $firstColor ?? 'Yellow Gold',
                                    ];

                                    if ($hasSizes) {
                                        $allSpecs[] = [
                                            'label' => 'Size',
                                            'is_alpine' => true,
                                            'alpine_text' => "selectedSize || 'Standard'",
                                            'static_value' => 'Standard',
                                        ];
                                    }

                                    if (!empty($product->diamond_carat)) {
                                        $allSpecs[] = [
                                            'label' => 'Diamond Weight',
                                            'is_alpine' => false,
                                            'static_value' => $product->diamond_carat,
                                        ];
                                    }

                                    if (!empty($product->diamond_color_clarity)) {
                                        $allSpecs[] = [
                                            'label' => 'Color & Clarity',
                                            'is_alpine' => false,
                                            'static_value' => $product->diamond_color_clarity,
                                        ];
                                    }

                                    if (!empty($product->diamond_cut)) {
                                        $allSpecs[] = [
                                            'label' => 'Cut / Shape',
                                            'is_alpine' => false,
                                            'static_value' => $product->diamond_cut,
                                        ];
                                    }

                                    if (!empty($product->certification)) {
                                        $allSpecs[] = [
                                            'label' => 'Certification',
                                            'is_alpine' => false,
                                            'static_value' => $product->certification,
                                        ];
                                    }

                                    if (!empty($product->sku)) {
                                        $allSpecs[] = [
                                            'label' => 'Product Code',
                                            'is_alpine' => false,
                                            'static_value' => $product->sku,
                                        ];
                                    }

                                    $allSpecs[] = [
                                        'label' => 'Crafting Type',
                                        'is_alpine' => false,
                                        'static_value' => $product->inventory_type === 'MADE_TO_ORDER' ? 'Custom Made to Order' : 'Ready to Ship',
                                    ];

                                    $specRows = array_chunk($allSpecs, 2);
                                @endphp

                                @foreach($specRows as $rIdx => $row)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 {{ !$loop->last ? 'border-b border-[#E6E1D7]' : '' }}">
                                        {{-- Left Cell (Always 50% width on desktop, NEVER full width) --}}
                                        <div class="flex items-center justify-between px-5 py-3.5 sm:border-r border-[#E6E1D7] hover:bg-white/60 transition-colors">
                                            <span class="text-[11px] uppercase tracking-[0.16em] text-[#8E877D] font-medium">{{ $row[0]['label'] }}</span>
                                            @if(!empty($row[0]['is_alpine']))
                                                <span class="text-xs sm:text-[13px] font-normal text-[#1C1917] tracking-normal" x-text="{{ $row[0]['alpine_text'] }}">{{ $row[0]['static_value'] }}</span>
                                            @else
                                                <span class="text-xs sm:text-[13px] font-normal text-[#1C1917] tracking-normal">{{ $row[0]['static_value'] }}</span>
                                            @endif
                                        </div>

                                        @if(isset($row[1]))
                                            {{-- Right Cell (Always 50% width on desktop) --}}
                                            <div class="flex items-center justify-between px-5 py-3.5 hover:bg-white/60 transition-colors border-t sm:border-t-0 border-[#E6E1D7]">
                                                <span class="text-[11px] uppercase tracking-[0.16em] text-[#8E877D] font-medium">{{ $row[1]['label'] }}</span>
                                                @if(!empty($row[1]['is_alpine']))
                                                    <span class="text-xs sm:text-[13px] font-normal text-[#1C1917] tracking-normal" x-text="{{ $row[1]['alpine_text'] }}">{{ $row[1]['static_value'] }}</span>
                                                @else
                                                    <span class="text-xs sm:text-[13px] font-normal text-[#1C1917] tracking-normal">{{ $row[1]['static_value'] }}</span>
                                                @endif
                                            </div>
                                        @else
                                            {{-- Clean Empty Cell: Preserves 50% width on left and maintains vertical center divider --}}
                                            <div class="hidden sm:block"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <style>
                        .artisan-rich-text {
                            color: #44403C;
                            font-size: 0.95rem;
                            line-height: 1.8;
                        }
                        .artisan-rich-text p {
                            margin-bottom: 1.25rem;
                        }
                        .artisan-rich-text strong, .artisan-rich-text b {
                            color: #1C1917;
                            font-weight: 600;
                        }
                        .artisan-rich-text h1, .artisan-rich-text h2, .artisan-rich-text h3, .artisan-rich-text h4 {
                            color: #1C1917;
                            font-family: 'Cormorant Garamond', Georgia, serif;
                            font-weight: 600;
                            margin-top: 1.75rem;
                            margin-bottom: 0.75rem;
                            line-height: 1.3;
                        }
                        .artisan-rich-text h1 { font-size: 1.75rem; }
                        .artisan-rich-text h2 { font-size: 1.5rem; }
                        .artisan-rich-text h3 { font-size: 1.25rem; }
                        .artisan-rich-text ul {
                            list-style-type: disc !important;
                            margin-top: 0.75rem;
                            margin-bottom: 1.25rem;
                            padding-left: 1.75rem !important;
                        }
                        .artisan-rich-text ol {
                            list-style-type: decimal !important;
                            margin-top: 0.75rem;
                            margin-bottom: 1.25rem;
                            padding-left: 1.75rem !important;
                        }
                        .artisan-rich-text li {
                            margin-bottom: 0.5rem;
                            padding-left: 0.25rem;
                            color: #57534E;
                        }
                        .artisan-rich-text blockquote {
                            border-left: 3.5px solid #C8A96E;
                            background: rgba(245, 242, 235, 0.75);
                            padding: 1rem 1.25rem;
                            margin: 1.5rem 0;
                            border-radius: 0 0.75rem 0.75rem 0;
                            font-style: italic;
                            color: #292524;
                        }
                        .artisan-rich-text a {
                            color: #9A7B4F;
                            text-decoration: underline;
                        }
                    </style>
                    <div x-show="activeTab === 'shipping'" x-cloak class="text-sm text-[#78716C] font-light leading-relaxed space-y-4">
                        @php
                            $shippingPage = \App\Models\PolicyPage::findBySlug('shipping');
                            $returnPage   = \App\Models\PolicyPage::findBySlug('return');

                            $shippingNote = \App\Models\SiteSetting::get('product_tab_shipping_note', 'Ships within 24–48 business hours.');

                            $c1Title = \App\Models\SiteSetting::get('product_tab_ship_badge_1_title', 'Free Ship');
                            $c1Sub   = \App\Models\SiteSetting::get('product_tab_ship_badge_1_subtitle', 'WORLDWIDE');

                            $c2Title = \App\Models\SiteSetting::get('product_tab_ship_badge_2_title', '24–48 hrs');
                            $c2Sub   = \App\Models\SiteSetting::get('product_tab_ship_badge_2_subtitle', 'DISPATCH');

                            $c3Title = \App\Models\SiteSetting::get('product_tab_ship_badge_3_title', 'Insured');
                            $c3Sub   = \App\Models\SiteSetting::get('product_tab_ship_badge_3_subtitle', 'PACKAGING');

                            $b1 = \App\Models\SiteSetting::get('product_tab_policy_bullet_1', 'Orders can be modified or cancelled within 3 hours');
                            $b2 = \App\Models\SiteSetting::get('product_tab_policy_bullet_2', '100% insured courier delivery with signature confirmation');
                            $b3 = \App\Models\SiteSetting::get('product_tab_policy_bullet_3', 'Complimentary ring sizing & lifetime authenticity guarantee');
                        @endphp

                        {{-- Shipping line --}}
                        <p class="text-[13px] text-[#524C46]">
                            {{ $shippingNote }}
                        </p>

                        {{-- Shipping highlights grid --}}
                        <div class="grid grid-cols-3 gap-2 sm:gap-2.5 pt-1">
                            <div class="bg-[#FAF8F5] border border-[#DFD9CE] rounded-xl p-2 sm:p-3 text-center overflow-hidden">
                                <p class="text-[10px] sm:text-[11px] font-semibold text-[#1C1917] truncate">{{ $c1Title }}</p>
                                <p class="text-[8px] sm:text-[9px] uppercase tracking-[0.08em] sm:tracking-[0.12em] text-[#8E877D] mt-0.5 truncate">{{ $c1Sub }}</p>
                            </div>
                            <div class="bg-[#FAF8F5] border border-[#DFD9CE] rounded-xl p-2 sm:p-3 text-center overflow-hidden">
                                <p class="text-[10px] sm:text-[11px] font-semibold text-[#1C1917] truncate">{{ $c2Title }}</p>
                                <p class="text-[8px] sm:text-[9px] uppercase tracking-[0.08em] sm:tracking-[0.12em] text-[#8E877D] mt-0.5 truncate">{{ $c2Sub }}</p>
                            </div>
                            <div class="bg-[#FAF8F5] border border-[#DFD9CE] rounded-xl p-2 sm:p-3 text-center overflow-hidden">
                                <p class="text-[10px] sm:text-[11px] font-semibold text-[#1C1917] truncate">{{ $c3Title }}</p>
                                <p class="text-[8px] sm:text-[9px] uppercase tracking-[0.08em] sm:tracking-[0.12em] text-[#8E877D] mt-0.5 truncate">{{ $c3Sub }}</p>
                            </div>
                        </div>

                        {{-- Policy summary (short bullets only) --}}
                        <div class="pt-2 space-y-2">
                            <p class="text-[9px] uppercase tracking-[0.25em] font-semibold text-[#8E877D]">Cancellation & Returns</p>
                            <ul class="space-y-1.5 text-[12px] text-[#524C46] font-light">
                                <li class="flex items-start gap-2">
                                    <span class="text-[#B87333] mt-0.5 shrink-0">⏱</span>
                                    <span>{{ $b1 }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#8E877D] mt-0.5 shrink-0">✕</span>
                                    <span>{{ $b2 }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#4A7C4A] mt-0.5 shrink-0">✓</span>
                                    <span>{{ $b3 }}</span>
                                </li>
                            </ul>
                            @if($returnPage)
                            <a href="{{ route('legal.return') }}" class="inline-flex items-center gap-1 text-[10px] uppercase tracking-[0.2em] text-[#8E877D] hover:text-[#1C1917] transition-colors mt-1 font-medium">
                                Full policy →
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        {{-- ── CUSTOMER REVIEWS (INTERLINKED & DYNAMIC) ── --}}
        <div id="customer-reviews" class="pt-10 sm:pt-14 mb-16 sm:mb-20" x-data="{ showReviewForm: false, newRating: 5, visibleReviews: 6 }">
            @if(session('success'))
                <div class="mb-6">
                    <x-alert type="success" :message="session('success')" />
                </div>
            @endif

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 sm:gap-6 mb-8 sm:mb-10">
                <div>
                    <h2 class="font-editorial text-2xl sm:text-3xl lg:text-4xl text-[#1C1917] font-light mb-2">Customer Reviews.</h2>
                    <div class="flex items-center flex-wrap gap-2 sm:gap-3">
                        <span class="text-[#C8A96E] text-sm sm:text-base tracking-widest flex items-center">
                            @php
                                $avgRating = $product->average_rating ?? 5.0;
                                $fullStars = floor($avgRating);
                                $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                                $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                            @endphp
                            {!! str_repeat('★', $fullStars) !!}{!! $hasHalfStar ? '★' : '' !!}{!! str_repeat('☆', $emptyStars) !!}
                        </span>
                        <span class="text-xs sm:text-sm font-semibold text-[#1C1917]">{{ number_format($avgRating, 1) }} out of 5</span>
                        <span class="text-xs text-[#8E877D] font-light">({{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }})</span>
                    </div>
                </div>

                @auth
                    <button type="button" @click="showReviewForm = !showReviewForm"
                            class="bg-[#1C1917] hover:bg-[#2D2825] text-white text-[10px] uppercase tracking-[0.18em] sm:tracking-[0.2em] font-semibold px-5 sm:px-6 py-2.5 sm:py-3 rounded-full transition-all cursor-pointer shadow-xs self-start sm:self-auto">
                        <span x-text="showReviewForm ? 'Cancel Review' : '★ Write a Review'"></span>
                    </button>
                @else
                    <a href="{{ route('login', ['redirect' => request()->url()]) }}"
                       class="bg-[#1C1917] hover:bg-[#2D2825] text-white text-[10px] uppercase tracking-[0.18em] sm:tracking-[0.2em] font-semibold px-5 sm:px-6 py-2.5 sm:py-3 rounded-full transition-all cursor-pointer shadow-xs self-start sm:self-auto">
                        ★ Write a Review
                    </a>
                @endauth
            </div>

            {{-- Write Review Form (Glass Card Styling matching Auth & About pages) --}}
            <div x-show="showReviewForm" x-cloak
                 class="glass rounded-[1.5rem] sm:rounded-[2rem] p-4 sm:p-6 lg:p-8 mb-10 sm:mb-12 border border-[#DFD9CE] shadow-sm transition-all duration-300">
                <h3 class="font-editorial text-xl font-light text-[#1C1917] mb-1">Write your review</h3>
                <p class="text-xs text-[#8E877D] font-light mb-6">Share your experience with this bespoke atelier piece.</p>

                <form method="POST" action="{{ route('reviews.store') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="rating" :value="newRating">

                    {{-- Star Picker --}}
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.2em] font-semibold text-[#1C1917] mb-1.5">Rating *</label>
                        <div class="flex items-center gap-2">
                            <template x-for="star in [1, 2, 3, 4, 5]">
                                <button type="button" @click="newRating = star"
                                        class="text-2xl transition-transform hover:scale-125 focus:outline-none cursor-pointer"
                                        :class="star <= newRating ? 'text-[#C8A96E]' : 'text-[#DFD9CE]'">
                                    ★
                                </button>
                            </template>
                            <span class="text-xs font-medium text-[#78716C] ml-2" x-text="newRating + ' / 5 Stars'"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] font-semibold text-[#1C1917] mb-1">Your Name *</label>
                            <input type="text" name="reviewer_name" required value="{{ Auth::check() ? Auth::user()->name : '' }}" placeholder="Enter your name"
                                   class="w-full bg-white/80 border border-[#DFD9CE] rounded-[1.125rem] px-4 sm:px-5 py-3 text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] font-semibold text-[#1C1917] mb-1">Review Title (Optional)</label>
                            <input type="text" name="title" placeholder="e.g. Breathtaking finish!"
                                   class="w-full bg-white/80 border border-[#DFD9CE] rounded-[1.125rem] px-4 sm:px-5 py-3 text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.2em] font-semibold text-[#1C1917] mb-1">Your Review *</label>
                        <textarea name="comment" rows="3" required placeholder="Write your thoughts about this piece..."
                                  class="w-full bg-white/80 border border-[#DFD9CE] rounded-[1.125rem] p-4 text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] transition-all"></textarea>
                    </div>

                    <button type="submit"
                            class="bg-[#1C1917] hover:bg-[#2D2825] text-white text-[10px] uppercase tracking-[0.25em] font-semibold px-8 py-3.5 rounded-full transition-all cursor-pointer shadow-xs">
                        Submit Review
                    </button>
                </form>
            </div>

            {{-- Reviews Grid (Responsive 1 col mobile, 2 col tablet, 3 col desktop) --}}
            @if($product->reviews && $product->reviews->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($product->reviews as $index => $review)
                        <div class="glass rounded-[1.25rem] sm:rounded-[1.75rem] border border-[#DFD9CE] p-4 sm:p-6 space-y-3.5 sm:space-y-4 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between"
                             x-show="{{ $index }} < visibleReviews" x-cloak>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="text-[#C8A96E] text-xs tracking-widest">
                                        {{ str_repeat('★', $review->rating) }}
                                    </div>
                                </div>

                                @if($review->title)
                                    <p class="font-editorial text-base sm:text-lg text-[#1C1917] font-normal leading-snug">{{ $review->title }}</p>
                                @endif

                                <p class="text-xs sm:text-[12.5px] text-[#78716C] font-light leading-relaxed italic">"{{ $review->comment }}"</p>
                            </div>

                            <div class="pt-3 border-t border-[#DFD9CE]/60 flex items-center justify-between">
                                <span class="text-[9px] sm:text-[9.5px] uppercase tracking-[0.18em] text-[#1C1917] font-semibold truncate max-w-[140px]">
                                    {{ strtoupper($review->reviewer_name) }}
                                </span>
                                <span class="text-[8.5px] sm:text-[9px] uppercase tracking-[0.15em] text-[#8E877D] font-light shrink-0">
                                    {{ $review->created_at->format('M Y') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Load More / Show Less Button --}}
                @if($product->reviews->count() > 6)
                    <div class="text-center pt-8">
                        <button type="button" @click="visibleReviews = visibleReviews >= {{ $product->reviews->count() }} ? 6 : {{ $product->reviews->count() }}"
                                class="bg-white border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-[10px] uppercase tracking-[0.2em] sm:tracking-[0.25em] font-semibold px-6 sm:px-8 py-3 sm:py-3.5 rounded-full transition-all cursor-pointer shadow-2xs">
                            <span x-text="visibleReviews >= {{ $product->reviews->count() }} ? 'SHOW LESS REVIEWS' : 'LOAD MORE REVIEWS ({{ $product->reviews->count() - 6 }} MORE)'"></span>
                        </button>
                    </div>
                @endif
            @else
                <div class="glass rounded-[1.5rem] sm:rounded-[1.75rem] border border-[#DFD9CE] p-6 sm:p-8 text-center space-y-2">
                    <p class="font-editorial text-lg sm:text-xl italic text-[#1C1917]">No reviews yet.</p>
                    <p class="text-xs text-[#8E877D] font-light">Be the first collector to share your experience with this piece.</p>
                </div>
            @endif
        </div>

        {{-- ── YOU MAY ALSO LOVE ────────────────────────────────── --}}
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div class="pt-10 sm:pt-14 mb-16 sm:mb-20">
                <h2 class="font-editorial text-2xl sm:text-3xl lg:text-4xl text-[#1C1917] font-light mb-6 sm:mb-8">You may also love.</h2>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                    @foreach($relatedProducts->take(4) as $rel)
                        <div class="group">
                            <div class="relative overflow-hidden aspect-[4/5] rounded-[1.25rem] sm:rounded-[2rem] bg-[#F5F2EB] border border-[#DFD9CE]/60 shadow-2xs mb-2.5 sm:mb-3 group/card">
                                <a href="{{ route('shop.show', $rel->slug) }}" class="block w-full h-full">
                                    @if(!empty($rel->images) && isset($rel->images[0]))
                                        <img src="{{ $rel->images[0] }}" alt="{{ $rel->name }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                    @endif
                                </a>



                                {{-- Action Buttons (Visible on mobile, hover-animated on desktop) --}}
                                @php
                                    $relWishlisted = false;
                                    if (Auth::check()) {
                                        $relWishlisted = \App\Models\Wishlist::where('user_id', Auth::id())->where('product_id', $rel->id)->exists();
                                    } else {
                                        $relWishlisted = in_array($rel->id, session('guest_wishlist', []));
                                    }
                                @endphp
                                <div class="absolute top-2 sm:top-3 right-2 sm:right-3 flex flex-col space-y-1 sm:space-y-1.5 z-20 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 sm:translate-x-2 sm:group-hover:translate-x-0 transition-all duration-300 ease-out">
                                    <form method="POST" action="{{ route('wishlist.toggle') }}" class="wishlist-toggle-form" data-product-id="{{ $rel->id }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $rel->id }}">
                                        <button type="submit" title="{{ $relWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}"
                                                class="wishlist-btn w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center transition-all duration-200 cursor-pointer shadow-xs {{ $relWishlisted ? 'bg-[#1C1917] text-white' : 'glass-pill border border-white/70 text-[#1C1917] hover:bg-[#1C1917] hover:text-white' }}"
                                                data-product-id="{{ $rel->id }}"
                                                data-wishlisted="{{ $relWishlisted ? 'true' : 'false' }}"
                                                data-style-type="related-item">
                                            <svg class="wishlist-icon w-3 sm:w-3.5 h-3 sm:h-3.5 stroke-[1.75] transition-all duration-200 {{ $relWishlisted ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                            </svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('cart.add') }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $rel->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" title="Add to Bag" class="glass-pill border border-white/70 w-7 h-7 sm:w-8 sm:h-8 rounded-full text-[#1C1917] hover:bg-[#1C1917] hover:text-white flex items-center justify-center transition-all duration-200 cursor-pointer">
                                            <svg class="w-3 sm:w-3.5 h-3 sm:h-3.5 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="flex items-start justify-between gap-3 px-1 mt-3">
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-editorial text-sm sm:text-base text-[#1C1917] font-normal leading-snug truncate">
                                        <a href="{{ route('shop.show', $rel->slug) }}" class="hover:text-[#8E877D] transition-colors">{{ $rel->name }}</a>
                                    </h3>
                                    <p class="text-[10px] sm:text-[11px] text-[#8E877D] font-light mt-0.5 truncate">{{ $rel->category?->name ?? 'Fine Jewellery' }}</p>
                                </div>
                                <div class="text-right shrink-0 pt-0.5">
                                    <span class="text-xs sm:text-[13.5px] font-semibold text-[#1C1917] whitespace-nowrap">₹{{ number_format($rel->effective_price) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── RECENTLY VIEWED ──────────────────────────────────── --}}
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div class="pt-10 sm:pt-14 mb-16 sm:mb-20">
                <h2 class="font-editorial text-2xl sm:text-3xl lg:text-4xl text-[#1C1917] font-light mb-6 sm:mb-8">Recently viewed.</h2>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                    @foreach($relatedProducts->take(4) as $recent)
                        <div class="group">
                            <div class="relative overflow-hidden aspect-[4/5] rounded-[1.25rem] sm:rounded-[2rem] bg-[#F5F2EB] border border-[#DFD9CE]/60 shadow-2xs mb-2.5 sm:mb-3 group/card">
                                <a href="{{ route('shop.show', $recent->slug) }}" class="block w-full h-full">
                                    @if(!empty($recent->images) && isset($recent->images[0]))
                                        <img src="{{ $recent->images[0] }}" alt="{{ $recent->name }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                    @endif
                                </a>

                                {{-- Action Buttons --}}
                                @php
                                    $recentWishlisted = false;
                                    if (Auth::check()) {
                                        $recentWishlisted = \App\Models\Wishlist::where('user_id', Auth::id())->where('product_id', $recent->id)->exists();
                                    } else {
                                        $recentWishlisted = in_array($recent->id, session('guest_wishlist', []));
                                    }
                                @endphp
                                <div class="absolute top-2 sm:top-3 right-2 sm:right-3 flex flex-col space-y-1 sm:space-y-1.5 z-20 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 sm:translate-x-2 sm:group-hover:translate-x-0 transition-all duration-300 ease-out">
                                    <form method="POST" action="{{ route('wishlist.toggle') }}" class="wishlist-toggle-form" data-product-id="{{ $recent->id }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $recent->id }}">
                                        <button type="submit" title="{{ $recentWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}"
                                                class="wishlist-btn w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center transition-all duration-200 cursor-pointer shadow-xs {{ $recentWishlisted ? 'bg-[#1C1917] text-white' : 'glass-pill border border-white/70 text-[#1C1917] hover:bg-[#1C1917] hover:text-white' }}"
                                                data-product-id="{{ $recent->id }}"
                                                data-wishlisted="{{ $recentWishlisted ? 'true' : 'false' }}"
                                                data-style-type="related-item">
                                            <svg class="wishlist-icon w-3 sm:w-3.5 h-3 sm:h-3.5 stroke-[1.75] transition-all duration-200 {{ $recentWishlisted ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                            </svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('cart.add') }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $recent->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" title="Add to Bag" class="glass-pill border border-white/70 w-7 h-7 sm:w-8 sm:h-8 rounded-full text-[#1C1917] hover:bg-[#1C1917] hover:text-white flex items-center justify-center transition-all duration-200 cursor-pointer">
                                            <svg class="w-3 sm:w-3.5 h-3 sm:h-3.5 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="flex items-start justify-between gap-3 px-1 mt-3">
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-editorial text-sm sm:text-base text-[#1C1917] font-normal leading-snug truncate">
                                        <a href="{{ route('shop.show', $recent->slug) }}" class="hover:text-[#8E877D] transition-colors">{{ $recent->name }}</a>
                                    </h3>
                                    <p class="text-[10px] sm:text-[11px] text-[#8E877D] font-light mt-0.5 truncate">{{ $recent->category?->name ?? 'Fine Jewellery' }}</p>
                                </div>
                                <div class="text-right shrink-0 pt-0.5">
                                    <span class="text-xs sm:text-[13.5px] font-semibold text-[#1C1917] whitespace-nowrap">₹{{ number_format($recent->effective_price) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>

{{-- Global Luxury Jewelry Image Protection & Anti-Theft Script --}}
<style>
    /* Prevent dragging or highlighting luxury jewelry photos */
    img, video {
        -webkit-user-drag: none !important;
        -khtml-user-drag: none !important;
        -moz-user-drag: none !important;
        -o-user-drag: none !important;
        user-drag: none !important;
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
        user-select: none !important;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Prevent drag on all images on product page
        document.querySelectorAll('img').forEach(function (img) {
            img.setAttribute('draggable', 'false');
            img.addEventListener('dragstart', function (e) {
                e.preventDefault();
                return false;
            });
        });

        // Prevent right-click context menu save image on gallery
        const gallery = document.querySelector('[x-data*="productDetail"]');
        if (gallery) {
            gallery.addEventListener('contextmenu', function (e) {
                if (e.target.tagName === 'IMG' || e.target.closest('img')) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    });
</script>
</x-app-layout>
