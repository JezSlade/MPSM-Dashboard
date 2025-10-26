using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{
    /// <summary>
    /// Represent an office
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class OfficeBaseDto : EntityDto
    {
        public OfficeBaseDto()
        {
        }

        /// <summary>
        /// Gets or sets the code.
        /// </summary>
        /// <value>
        /// The code.
        /// </value>
        [DataMember]
        public string Code { get; set; }

        /// <summary>
        /// Gets or sets the description.
        /// </summary>
        /// <value>
        /// The description.
        /// </value>
        [DataMember]

        public string Description { get; set; }
    }

    /// <summary>
    /// Represent an office
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class OfficeDto : OfficeBaseDto
    {
        public OfficeDto()
        {
            this.OfficeSubnets = new List<OfficeSubnetDto>();
            this.CustomFieldValues = new List<CustomFieldValueDto>();
        }

        /// <summary>
        /// Gets or sets the customer dto.
        /// </summary>
        /// <value>
        /// The customer dto.
        /// </value>
        [DataMember]
        public CustomerBaseDto Customer { get; set; }

        /// <summary>
        /// Gets or sets the DestinationCode.
        /// </summary>
        /// <value>
        /// The description.
        /// </value>
        [DataMember]
        public string DestinationCode { get; set; }

        /// <summary>
        /// Gets or sets the address.
        /// </summary>
        /// <value>
        /// The address.
        /// </value>
        [DataMember]
        public string Address { get; set; }

        /// <summary>
        /// Gets or sets the Sap.
        /// </summary>
        /// <value>
        /// The address.
        /// </value>
        [DataMember]
        public string Sap { get; set; }


        /// <summary>
        /// Gets or sets the telephone.
        /// </summary>
        /// <value>
        /// The telephone.
        /// </value>
        [DataMember]
        public string Telephone { get; set; }

        /// <summary>
        /// Gets or sets the fax.
        /// </summary>
        /// <value>
        /// The fax.
        /// </value>
        [DataMember]
        public string Fax { get; set; }

        /// <summary>
        /// Gets or sets the city.
        /// </summary>
        /// <value>
        /// The city.
        /// </value>
        [DataMember]
        public string City { get; set; }

        /// <summary>
        /// Gets or sets the zip code.
        /// </summary>
        /// <value>
        /// The zip code.
        /// </value>
        [DataMember]
        public string ZipCode { get; set; }

        /// <summary>
        /// Gets or sets the province.
        /// </summary>
        /// <value>
        /// The province.
        /// </value>
        [DataMember]
        public string Province { get; set; }

        /// <summary>
        /// Gets or sets the Country.
        /// </summary>
        /// <value>
        /// The Country.
        /// </value>
        [DataMember]
        [Obsolete("Use IdCountry")]
        public string Country { get; set; }

        /// <summary>
        /// Gets or sets the Country Id.
        /// </summary>
        /// <value>
        /// The Country Id.
        /// </value>
        [DataMember]
        public string IdCountry { get; set; }


        /// <summary>
        /// Gets or sets the latitude.
        /// </summary>
        /// <value>
        /// The latitude.
        /// </value>
        [DataMember]
        public float? Lat { get; set; }

        /// <summary>
        /// Gets or sets the Longitute.
        /// </summary>
        /// <value>
        /// The Longitute.
        /// </value>
        [DataMember]
        public float? Lng { get; set; }

        /// <summary>
        /// Gets or sets the mail address.
        /// </summary>
        /// <value>
        /// The mail address.
        /// </value>
        [DataMember]
        public string MailAddress { get; set; }

        /// <summary>
        /// Gets or sets the DeliveryTonerMails.
        /// </summary>
        /// <value>
        /// The deliveryTonerMails.
        /// </value>
        [DataMember]
        public string DeliveryTonerMails { get; set; }

        /// <summary>
        /// Gets or sets the destinationDescription.
        /// </summary>
        /// <value>
        /// The destinationDescription.
        /// </value>
        [DataMember]
        public string DestinationDescription { get; set; }

        /// <summary>
        /// Gets or sets the EnableAutoAssign.
        /// </summary>
        /// <value>
        /// The EnableAutoAssign.
        /// </value>
        [DataMember]
        public bool EnableAutoAssign { get; set; }

        /// <summary>
        /// Gets or sets the Note.
        /// </summary>
        /// <value>
        /// The Note.
        /// </value>
        [DataMember]
        public string Note { get; set; }


        /// <summary>
        /// Gets or sets the customer identifier.
        /// </summary>
        /// <value>
        /// The customer identifier.
        /// </value>
        [DataMember]
        [Obsolete("Use Customer.Code instead")]
        public string CustomerId { get; set; }

        /// <summary>
        /// Gets or sets the devices Numbers
        /// </summary>
        /// <value>
        /// The customer identifier.
        /// </value>
        [DataMember]
        public int AvailableDevices { get; set; }

        /// <summary>
        /// Gets or sets the subnets
        /// </summary>
        /// <value>
        /// The subnets.
        /// </value>
        [DataMember]
        public List<OfficeSubnetDto> OfficeSubnets { get; set; }

        /// <summary>
        /// Gets or sets the custom fields
        /// </summary>
        /// <value>
        /// The subnets.
        /// </value>
        [DataMember]
        public List<CustomFieldValueDto> CustomFieldValues { get; set; }


        /// <summary>
        /// Gets or sets the external identifier.
        /// </summary>
        /// <value>
        /// The external identifier.
        /// </value>
        [DataMember]
        public string ExternalIdentifier { get; set; }
    }

    /// <summary>
    /// 
    /// </summary>
    public class OfficeSubnetDto : EntityDto
    {
        /// <summary>
        /// Gets or sets the IpRootFrom
        /// </summary>
        /// <value>
        /// The IpRootFrom.
        /// </value>
        [DataMember]
        public string IpRootFrom { get; set; }

        /// <summary>
        /// Gets or sets the IpRootTo
        /// </summary>
        /// <value>
        /// The IpRootTo.
        /// </value>
        [DataMember]
        public string IpRootTo { get; set; }

    }


    /// <summary>
    /// Represent an office
    /// </summary>
    /// <seealso cref="EntityDto" />
    [DataContract]
    public class OfficeListDto : EntityDto
    {
        public OfficeListDto()
        {
            this.OfficeSubnets = new List<OfficeSubnetDto>();
        }

        /// <summary>
        /// Gets or sets the customer dto.
        /// </summary>
        /// <value>
        /// The customer dto.
        /// </value>
        [DataMember]
        public CustomerBaseDto Customer { get; set; }

        /// <summary>
        /// Gets or sets the code.
        /// </summary>
        /// <value>
        /// The code.
        /// </value>
        [DataMember]
        public string Code { get; set; }

        /// <summary>
        /// Gets or sets the description.
        /// </summary>
        /// <value>
        /// The description.
        /// </value>
        [DataMember]
        public string Description { get; set; }

        /// <summary>
        /// Gets or sets the DestinationCode.
        /// </summary>
        /// <value>
        /// The description.
        /// </value>
        [DataMember]
        public string DestinationCode { get; set; }

        /// <summary>
        /// Gets or sets the address.
        /// </summary>
        /// <value>
        /// The address.
        /// </value>
        [DataMember]
        public string Address { get; set; }

        /// <summary>
        /// Gets or sets the Sap.
        /// </summary>
        /// <value>
        /// The address.
        /// </value>
        [DataMember]
        public string Sap { get; set; }


        /// <summary>
        /// Gets or sets the telephone.
        /// </summary>
        /// <value>
        /// The telephone.
        /// </value>
        [DataMember]

        public string Telephone { get; set; }

        /// <summary>
        /// Gets or sets the fax.
        /// </summary>
        /// <value>
        /// The fax.
        /// </value>
        [DataMember]
        public string Fax { get; set; }

        /// <summary>
        /// Gets or sets the city.
        /// </summary>
        /// <value>
        /// The city.
        /// </value>
        [DataMember]
        public string City { get; set; }

        /// <summary>
        /// Gets or sets the zip code.
        /// </summary>
        /// <value>
        /// The zip code.
        /// </value>
        [DataMember]
        public string ZipCode { get; set; }

        /// <summary>
        /// Gets or sets the province.
        /// </summary>
        /// <value>
        /// The province.
        /// </value>
        [DataMember]
        public string Province { get; set; }

        /// <summary>
        /// Gets or sets the Country.
        /// </summary>
        /// <value>
        /// The Country.
        /// </value>
        [DataMember]
        public string Country { get; set; }

        /// <summary>
        /// Gets or sets the Country Id.
        /// </summary>
        /// <value>
        /// The Country Id.
        /// </value>
        [DataMember]
        public string IdCountry { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is country eu.
        /// </summary>
        /// <value>
        /// <c>true</c> if this instance is country eu; otherwise, <c>false</c>.</value>
        [DataMember]
        public bool IsCountryEu { get; set; }

        /// <summary>
        /// Gets or sets the latitude.
        /// </summary>
        /// <value>
        /// The latitude.
        /// </value>
        [DataMember]
        public float? Lat { get; set; }

        /// <summary>
        /// Gets or sets the Longitute.
        /// </summary>
        /// <value>
        /// The Longitute.
        /// </value>
        [DataMember]
        public float? Lng { get; set; }

        /// <summary>
        /// Gets or sets the mail address.
        /// </summary>
        /// <value>
        /// The mail address.
        /// </value>
        [DataMember]

        public string MailAddress { get; set; }

        /// <summary>
        /// Gets or sets the DeliveryTonerMails.
        /// </summary>
        /// <value>
        /// The deliveryTonerMails.
        /// </value>
        [DataMember]

        public string DeliveryTonerMails { get; set; }

        /// <summary>
        /// Gets or sets the destinationDescription.
        /// </summary>
        /// <value>
        /// The destinationDescription.
        /// </value>
        [DataMember]
        public string DestinationDescription { get; set; }

        /// <summary>
        /// Gets or sets the EnableAutoAssign.
        /// </summary>
        /// <value>
        /// The EnableAutoAssign.
        /// </value>
        [DataMember]
        public bool? EnableAutoAssign { get; set; }

        /// <summary>
        /// Gets or sets the Note.
        /// </summary>
        /// <value>
        /// The Note.
        /// </value>
        [DataMember]
        public string Note { get; set; }


        /// <summary>
        /// Gets or sets the customer identifier.
        /// </summary>
        /// <value>
        /// The customer identifier.
        /// </value>
        [DataMember]
        [Obsolete("Use Customer.Code instead")]
        public string CustomerId { get; set; }

        /// <summary>
        /// Gets or sets the devices Numbers
        /// </summary>
        /// <value>
        /// The customer identifier.
        /// </value>
        [DataMember]
        public int AvailableDevices { get; set; }

        /// <summary>
        /// Gets or sets the subnets
        /// </summary>
        /// <value>
        /// The subnets.
        /// </value>
        [DataMember]
        public List<OfficeSubnetDto> OfficeSubnets { get; set; }

        /// <summary>
        /// Gets or sets the external identifier.
        /// </summary>
        /// <value>
        /// The external identifier.
        /// </value>
        [DataMember]
        public string ExternalIdentifier { get; set; }
    }
}
