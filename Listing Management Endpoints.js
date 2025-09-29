// routes/listings.js
const express = require('express');
const router = express.Router();
const Listing = require('../models/Listing');
const adminAuth = require('../middleware/adminAuth');

// Create listing with photos
router.post('/', adminAuth, async (req, res) => {
  try {
    const {
      title,
      description,
      price,
      make,
      model,
      year,
      mileage,
      fuelType,
      transmission,
      photos,
      status
    } = req.body;

    const listing = new Listing({
      title,
      description,
      price,
      make,
      model,
      year,
      mileage,
      fuelType,
      transmission,
      photos: photos || [],
      status: status || 'draft',
      createdBy: req.admin.id
    });

    await listing.save();
    res.status(201).json(listing);
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
});

// Update listing photos
router.patch('/:id/photos', adminAuth, async (req, res) => {
  try {
    const { photos } = req.body;
    const listing = await Listing.findById(req.params.id);

    if (!listing) {
      return res.status(404).json({ error: 'Listing not found' });
    }

    listing.photos = photos;
    listing.updatedAt = Date.now();
    
    await listing.save();
    res.json(listing);
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
});

// Deploy/publish listing
router.patch('/:id/deploy', adminAuth, async (req, res) => {
  try {
    const listing = await Listing.findById(req.params.id);

    if (!listing) {
      return res.status(404).json({ error: 'Listing not found' });
    }

    if (listing.photos.length === 0) {
      return res.status(400).json({ error: 'Cannot deploy listing without photos' });
    }

    listing.status = 'published';
    listing.publishedAt = Date.now();
    listing.updatedAt = Date.now();

    await listing.save();
    res.json({ message: 'Listing deployed successfully', listing });
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
});

// Get all listings (with filtering)
router.get('/', adminAuth, async (req, res) => {
  try {
    const { status, page = 1, limit = 10 } = req.query;
    const query = status ? { status } : {};

    const listings = await Listing.find(query)
      .sort({ createdAt: -1 })
      .limit(limit * 1)
      .skip((page - 1) * limit);

    const total = await Listing.countDocuments(query);

    res.json({
      listings,
      totalPages: Math.ceil(total / limit),
      currentPage: page,
      total
    });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

module.exports = router;