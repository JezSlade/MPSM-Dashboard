using MpsMonitor.Sdk.Models.Enums;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{
    /// <summary>
    /// Represent an shipped supply
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class ShippedSupplyDto : EntityDto
    {
        /// <summary>
        /// Initializes a new instance of the <see cref="ShippedSupplyDto"/> class.
        /// </summary>
        public ShippedSupplyDto()
        {
            this.SupplyAlerts = new List<SupplyAlertForShippedSupplyDto>();
        }

        /// <summary>
        /// Gets or sets the dealer id.
        /// </summary>
        /// <value>
        /// The dealer code.
        /// </value>
        [DataMember]
        public string DealerId { get; set; }

        /// <summary>
        /// Gets or sets the dealer code.
        /// </summary>
        /// <value>
        /// The dealer code.
        /// </value>
        [DataMember]
        public string DealerCode { get; set; }

        /// <summary>
        /// Gets or sets the dealer description.
        /// </summary>
        /// <value>
        /// The dealer description.
        /// </value>
        [DataMember]
        public string DealerDescription { get; set; }

        /// <summary>
        /// Gets or sets the customer id.
        /// </summary>
        /// <value>
        /// The customer code.
        /// </value>
        [DataMember]
        public string CustomerId { get; set; }

        /// <summary>
        /// Gets or sets the customer code.
        /// </summary>
        /// <value>
        /// The customer code.
        /// </value>
        [DataMember]
        public string CustomerCode { get; set; }

        /// <summary>
        /// Gets or sets the customer description.
        /// </summary>
        /// <value>
        /// The customer description.
        /// </value>
        [DataMember]
        public string CustomerDescription { get; set; }


        /// <summary>
        /// Gets or sets the supply.
        /// </summary>
        /// <value>
        /// The supply.
        /// </value>
        [DataMember]
        public SupplyBaseDto Supply { get; set; }

        /// <summary>
        /// Gets or sets the InstalledProductd Id
        /// </summary>
        /// <value>
        /// The quantity.
        /// </value>
        [DataMember]
        public string InstalledProductId { get; set; }

        ////[DataMember]
        ////public string Code { get; set; }

        /// <summary>
        /// Gets or sets the serial number
        /// </summary>
        /// <value>
        /// The SerialNumber.
        /// </value>
        [DataMember]
        public string SerialNumber { get; set; }

        /// <summary>
        /// Gets or sets the asset number
        /// </summary>
        /// <value>
        /// The AssetNumber.
        /// </value>
        [DataMember]
        public string AssetNumber { get; set; }

        /// <summary>
        /// Gets or sets the system name
        /// </summary>
        /// <value>
        /// The AssetNumber.
        /// </value>
        [DataMember]
        public string SystemName { get; set; }




        /// <summary>
        /// Gets or sets the Product
        /// </summary>
        /// <value>
        /// The Product.
        /// </value>
        [DataMember]
        public ProductBaseDto Product { get; set; }

        /// <summary>
        /// Gets or sets the Project
        /// </summary>
        /// <value>
        /// The Project.
        /// </value>
        [DataMember]
        public ProjectDto Project { get; set; }

        /// <summary>
        /// Gets or sets the Office
        /// </summary>
        /// <value>
        /// The Project.
        /// </value>
        [DataMember]
        public OfficeDto Office { get; set; }


        /// <summary>
        /// Gets or sets the quantity.
        /// </summary>
        /// <value>
        /// The quantity.
        /// </value>
        [DataMember]
        public int Quantity { get; set; }


        /// <summary>
        /// Gets or sets the partnumber.
        /// </summary>
        /// <value>
        /// The partnumber.
        /// </value>
        [DataMember]
        public string PartNumber { get; set; }

        /// <summary>Gets or sets the supply description.</summary>
        /// <value>The supply description.</value>
        [DataMember]
        public string SupplyDescription { get; set; }

        /// <summary>
        /// Gets or sets the stock quantity.
        /// </summary>
        /// <value>
        /// The stock quantity.
        /// </value>
        [DataMember]
        public int? StockQuantity { get; set; }

        /// <summary>
        /// Gets or sets the creation.
        /// </summary>
        /// <value>
        /// The creation.
        /// </value>
        [DataMember]
        public DateTime Creation { get; set; }

        /// <summary>
        /// Gets or sets the document number.
        /// </summary>
        /// <value>
        /// The document number.
        /// </value>
        [DataMember]
        public string DocumentNumber { get; set; }

        /// <summary>
        /// Gets or sets the order number.
        /// </summary>
        /// <value>
        /// The order number.
        /// </value>
        [DataMember]
        public string OrderNumber { get; set; }

        /// <summary>
        /// Gets or sets the tracking URL.
        /// </summary>
        /// <value>
        /// The tracking URL.
        /// </value>
        [DataMember]
        public string TrackingUrl { get; set; }

        /// <summary>
        /// Gets or sets the department.
        /// </summary>
        /// <value>
        /// The department.
        /// </value>
        [DataMember]
        public string Department { get; set; }

        /// <summary>
        /// Gets or sets the contact.
        /// </summary>
        /// <value>
        /// The contact.
        /// </value>
        [DataMember]
        public string Contact { get; set; }

        /// <summary>
        /// Gets or sets the tracking number.
        /// </summary>
        /// <value>
        /// The tracking number.
        /// </value>
        [DataMember]
        public string TrackingNumber { get; set; }

        /// <summary>
        /// Gets or sets the courier description.
        /// </summary>
        /// <value>
        /// The courier description.
        /// </value>
        [DataMember]
        public string CourierDescription { get; set; }

        ////[DataMember]
        ////public  CourierDto Courier { get; set; }

        ////public  bool IsAutoGenerated { get; set; }

        /// <summary>
        /// Gets or sets the Generation.
        /// </summary>
        /// <value>
        /// The generation.
        /// </value>

        [DataMember]
        public GenerationsEnum Generation { get; set; }

        /// <summary>
        /// Gets or sets the counter.
        /// </summary>
        /// <value>
        /// The counter.
        /// </value>
        [DataMember]
        public int Counter { get; set; }

        /// <summary>
        /// Gets or sets the price.
        /// </summary>
        /// <value>
        /// The price.
        /// </value>
        [DataMember]
        public float Price { get; set; }

        /// <summary>
        /// Gets or sets the price retailer.
        /// </summary>
        /// <value>
        /// The price retailer.
        /// </value>
        [DataMember]
        public float PriceRetailer { get; set; }

        /// <summary>
        /// Gets or sets the esprinet order number.
        /// </summary>
        /// <value>
        /// The esprinet order number.
        /// </value>
        /// <remarks>Numero ordine Esprinet ottenuto ordinando una consegna</remarks>
        [DataMember]
        public string EsprinetOrderNumber { get; set; }


        /// <summary>
        /// Gets or sets the esprinet tracking status.
        /// </summary>
        /// <value>
        /// The esprinet tracking status.
        /// </value>
        /// <remarks>Stato del tracking Esprinet</remarks>
        [DataMember]
        public string EsprinetTrackingStatus { get; set; }

        /// <summary>
        /// Gets or sets the esprinet tracking last update.
        /// </summary>
        /// <value>
        /// The esprinet tracking last update.
        /// </value>
        /// <remarks>Data Ultimo aggiornamento tracking</remarks>
        [DataMember]
        public DateTime? EsprinetTrackingLastUpdate { get; set; }

        /// <value>
        /// The esprinet error code.
        /// </value>
        /// <remarks>(Codice errore in caso di elaborazione consegna fallita)</remarks>
        [DataMember]
        public string EsprinetErrorCode { get; set; }

        /// <summary>
        /// Data di processamento ordine da parte di Esprinet
        /// </summary>
        /// <value>
        /// The esprinet processed on.
        /// </value>
        [DataMember]
        public DateTime? EsprinetProcessedOn { get; set; }

        /// <summary>
        /// Gets or sets the supply alerts.
        /// </summary>
        /// <value>
        /// The supply alerts.
        /// </value>
        [DataMember]
        public IEnumerable<SupplyAlertForShippedSupplyDto> SupplyAlerts { get; set; }

        /// <summary>
        /// Gets or sets the time zone.
        /// </summary>
        /// <value>
        /// The time zone.
        /// </value>
        [DataMember]
        public string TimeZone { get; set; }

        /// <summary>
        /// Gets or sets the time zone.
        /// </summary>
        /// <value>
        /// The time zone.
        /// </value>
        [DataMember]
        public string TimeZoneIana { get; set; }

    }



    /// <summary>
    /// Represents a supply alert
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class SupplyAlertForShippedSupplyDto : EntityDto
    {
        /////// <summary>
        /////// Gets or sets the code.
        /////// </summary>
        /////// <value>
        /////// The code.
        /////// </value>



        /// <summary>
        /// Gets or sets the warning.
        /// </summary>
        /// <value>
        /// The warning.
        /// </value>
        [DataMember]
        public string Warning { get; set; }

        /// <summary>
        /// Gets or sets the initial date.
        /// </summary>
        /// <value>
        /// The initial date.
        /// </value>
        [DataMember]
        public DateTime? InitialDate { get; set; }

        /// <summary>
        /// Gets or sets the initial mono.
        /// </summary>
        /// <value>
        /// The initial mono.
        /// </value>
        [DataMember]
        public int InitialMono { get; set; }

        /// <summary>
        /// Gets or sets the initial color.
        /// </summary>
        /// <value>
        /// The initial color.
        /// </value>
        [DataMember]
        public int InitialColor { get; set; }

        /// <summary>
        /// Gets or sets the initial total.
        /// </summary>
        /// <value>
        /// The initial total.
        /// </value>
        [DataMember]
        public int InitialTotal { get; set; }

        /// <summary>
        /// Gets or sets the initial residual percentage.
        /// </summary>
        /// <value>
        /// The initial residual percentage.
        /// </value>
        [DataMember]
        public byte InitialResidualPercentage { get; set; }

        /// <summary>
        /// Gets or sets the hide alert limit.
        /// </summary>
        /// <value>
        /// The hide alert limit.
        /// </value>
        [DataMember]
        public int? HideAlertLimit { get; set; }

        /// <summary>
        /// Gets or sets the actual date.
        /// </summary>
        /// <value>
        /// The actual date.
        /// </value>
        [DataMember]
        public DateTime? ActualDate { get; set; }

        /// <summary>
        /// Gets or sets the actual mono.
        /// </summary>
        /// <value>
        /// The actual mono.
        /// </value>
        [DataMember]
        public int ActualMono { get; set; }

        /// <summary>
        /// Gets or sets the actual color.
        /// </summary>
        /// <value>
        /// The actual color.
        /// </value>
        [DataMember]
        public int ActualColor { get; set; }

        /// <summary>
        /// Gets or sets the actual total.
        /// </summary>
        /// <value>
        /// The actual total.
        /// </value>
        [DataMember]
        public int ActualTotal { get; set; }

        /// <summary>
        /// Gets or sets the actual residual percentage.
        /// </summary>
        /// <value>
        /// The actual residual percentage.
        /// </value>
        [DataMember]
        public int ActualResidualPercentage { get; set; }

        /// <summary>
        /// Gets or sets the canceled on.
        /// </summary>
        /// <value>
        /// The canceled on.
        /// </value>
        [DataMember]
        public DateTime? CanceledOn { get; set; }

        /// <summary>
        /// Gets or sets the toner delivered.
        /// </summary>
        /// <value>
        /// The toner delivered.
        /// </value>
        [DataMember]
        public DateTime? TonerDelivered { get; set; }

        /// <summary>
        /// Gets or sets the toner installed.
        /// </summary>
        /// <value>
        /// The toner installed.
        /// </value>
        [DataMember]
        public DateTime? TonerInstalled { get; set; }



        /// <summary>
        /// Gets or sets a value indicating whether this instance is hidden.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is hidden; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool? IsHidden { get; set; }

        /// <summary>
        /// Gets or sets the type of the color.
        /// </summary>
        /// <value>
        /// The type of the color.
        /// </value>
        [DataMember]
        public string ColorType { get; set; }

        /// <summary>
        /// Gets or sets the type of the supply.
        /// </summary>
        /// <value>
        /// The type of the supply.
        /// </value>
        [DataMember]
        public string SupplyType { get; set; }

        /// <summary>
        /// Gets or sets the type of the maintenanceKit type.
        /// </summary>
        /// <value>
        /// The type of maintenance kit.
        /// </value>
        [DataMember]
        public EntityIdDescIntDto MaintenanceKitType { get; set; }

        /// <summary>
        /// Gets or sets the color of the maintenance kit.
        /// </summary>
        /// <value>
        /// The color of the maintenance kit
        /// </value>
        [DataMember]
        public EntityIdDescIntDto MaintenanceKitColor { get; set; }

    }


    /// <summary>
    /// 
    /// </summary>
    public class SupplyBaseDto : EntityDto
    {
        /// <summary>
        /// Gets or sets the PartNumber.
        /// </summary>
        /// <value>
        /// The PartNumber.
        /// </value>
        [DataMember]
        public virtual string PartNumber { get; set; }

        /// <summary>
        /// Gets or sets the Description.
        /// </summary>
        /// <value>
        /// The Description.
        /// </value>
        [DataMember]
        public virtual string Description { get; set; }

        /// <summary>
        /// Gets or sets the DescriptionLocalized.
        /// </summary>
        /// <value>
        /// The Description.
        /// </value>
        [DataMember]
        public virtual string DescriptionLocalized { get; set; }

        /// <summary>
        /// Gets or sets the Color Type.
        /// </summary>
        /// <value>
        /// The Color Type.
        /// </value>
        [DataMember]
        public virtual ColorTypeEnum ColorType { get; set; }

        /// <summary>
        /// Gets or sets the Supply Type.
        /// </summary>
        /// <value>
        /// The Supply Type.
        /// </value>
        [DataMember]
        public virtual SupplyTypeEnum SupplyType { get; set; }


        /// <summary>
        /// Gets or sets the Maintenance Kit Type.
        /// </summary>
        /// <value>
        /// The MaintenanceKit Type.
        /// </value>
        [DataMember]
        public virtual int? MaintenanceKitType { get; set; }

        /// <summary>
        /// Gets or sets the Maintenance Kit Color
        /// </summary>
        /// <value>
        /// The MaintenanceKitColor color.
        /// </value>
        [DataMember]
        public virtual int? MaintenanceKitColor { get; set; }

    }

    
    /// <summary>
    /// Represents a request to update a shipped supply
    /// </summary>
    [DataContract]
    public class UpdateShippedSupplyDto
    {
        /// <summary>
        /// Gets or sets the supply alert identifier.
        /// </summary>
        /// <value>
        /// The supply alert identifier.
        /// </value>
        [DataMember]
        public string SupplyAlertId { get; set; }

        /// <summary>
        /// Gets or sets the price.
        /// </summary>
        /// <value>
        /// The price.
        /// </value>
        [DataMember]
        public float? Price { get; set; }

        /// <summary>
        /// Gets or sets the creation.
        /// </summary>
        /// <value>
        /// The creation.
        /// </value>
        [DataMember]
        public DateTime Creation { get; set; }

        /// <summary>
        /// Gets or sets the document number.
        /// </summary>
        /// <value>
        /// The document number.
        /// </value>
        [DataMember]
        public string DocumentNumber { get; set; }

        /// <summary>
        /// Gets or sets the order number.
        /// </summary>
        /// <value>
        /// The order number.
        /// </value>
        [DataMember]
        public string OrderNumber { get; set; }

        /// <summary>
        /// Gets or sets the department.
        /// </summary>
        /// <value>
        /// The department.
        /// </value>
        [DataMember]
        public string Department { get; set; }

        /// <summary>
        /// Gets or sets the contact.
        /// </summary>
        /// <value>
        /// The contact.
        /// </value>
        [DataMember]
        public string Contact { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether [send mail].
        /// </summary>
        /// <value>
        ///   <c>true</c> if [send mail]; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool SendMail { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether [send logistic mail].
        /// </summary>
        /// <value>
        ///   <c>true</c> if [send logistic mail]; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool SendLogisticMail { get; set; }
    }
}
