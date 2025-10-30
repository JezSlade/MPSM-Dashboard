using MpsMonitor.Sdk.Models.Common;
using MpsMonitor.Sdk.Models.Enums;
using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{
    /// <summary>
    /// Device counter fo pages
    /// </summary>
    [DataContract]
    public class CounterDto : EntityDto
    {
        /// <summary>
        /// The date when the counter was metered
        /// </summary>
        [DataMember]
        public DateTime Received { get; set; }

        /// <summary>
        /// The value of mono counter
        /// </summary>
        [DataMember]
        public int? Mono { get; set; }

        /// <summary>
        /// The value of color counter
        /// </summary>
        [DataMember]
        public int? Color { get; set; }

        /// <summary>
        /// The value of A3 mono counter
        /// </summary>
        [DataMember]
        public int? MonoA3 { get; set; }


        /// <summary>
        /// The value of A3 color counter
        /// </summary>
        [DataMember]
        public int? ColorA3 { get; set; }

        /// <summary>
        /// The value of fax counter
        /// </summary>
        [DataMember]
        public int? Fax { get; set; }

        /// <summary>
        /// The account or system name where the counter comes from
        /// </summary>
        [DataMember]
        public string AccountName { get; set; }

    }

    /// <summary>
    /// 
    /// 
    /// </summary>
    [DataContract]
    public class CountersDetailedDeviceDto
    {
        /// <summary>
        /// Constructor
        /// </summary>
        public CountersDetailedDeviceDto()
        {
            CountersDetailed = new List<CounterDetailedDto>();
        }

        /// <summary>
        /// Customer Code
        /// </summary>
        [DataMember]
        public string CustomerCode { get; set; }

        /// <summary>
        /// Serial Number
        /// </summary>
        [DataMember]
        public string SerialNumber { get; set; }

        /// <summary>
        /// Asset Number
        /// </summary>
        [DataMember]
        public string AssetNumber { get; set; }

        /// <summary>
        /// Counters
        /// </summary>
        [DataMember]
        public IList<CounterDetailedDto> CountersDetailed { get; set; }
    }


    /// <summary>
    /// 
    /// </summary>
    [DataContract]
    public class CounterDetailedDto : EntityDto
    {
        /// <summary>
        /// Gets or sets the creation.
        /// </summary>
        /// <value>
        /// The creation.
        /// </value>
        [DataMember]
        public virtual DateTime? Creation { get; set; }

        /// <summary>
        /// Gets or sets the type.
        /// </summary>
        /// <value>
        /// The type.
        /// </value>
        [DataMember]
        public virtual string Type { get; set; }

        /// <summary>
        /// Gets or sets the description.
        /// </summary>
        /// <value>
        /// The description.
        /// </value>
        [DataMember]
        public virtual string Description { get; set; }

        /// <summary>
        /// Gets or sets the mono.
        /// </summary>
        /// <value>
        /// The mono.
        /// </value>
        [DataMember]
        public virtual int? Mono { get; set; }

        /// <summary>
        /// Gets or sets the full color.
        /// </summary>
        /// <value>
        /// The full color.
        /// </value>
        [DataMember]
        public virtual int? FullColor { get; set; }

        /// <summary>
        /// Gets or sets the color of the single.
        /// </summary>
        /// <value>
        /// The color of the single.
        /// </value>
        [DataMember]
        public virtual int? SingleColor { get; set; }

        /// <summary>
        /// Gets or sets the color of the two.
        /// </summary>
        /// <value>
        /// The color of the two.
        /// </value>
        [DataMember]
        public virtual int? TwoColor { get; set; }

        /// <summary>
        /// Gets or sets the scans.
        /// </summary>
        /// <value>
        /// The scans.
        /// </value>
        [DataMember]
        public virtual int? Scans { get; set; }

        /// <summary>
        /// Tag
        /// </summary>
        [DataMember]
        public string Tag { get; set; }

        /// <summary>
        /// Gets or sets the source.
        /// </summary>
        /// <value>
        /// The color of the two.
        /// </value>
        [DataMember]
        public virtual CounterDetailSourceEnum? Source { get; set; }

        /// <summary>
        /// Gets or sets the total.
        /// </summary>
        /// <value>
        /// The total.
        /// </value>
        [DataMember]
        public virtual int? Total { get; set; }

    }

    /// <summary>
    /// Device
    /// </summary>
    public class CountersDeviceDto
    {
        /// <summary>
        /// Constructor
        /// </summary>
        public CountersDeviceDto()
        {
            Counters = new List<CountersCounterDto>();
        }

        /// <summary>
        /// Customer Code
        /// </summary>
        [DataMember]
        public string CustomerCode { get; set; }

        /// <summary>
        /// Serial Number
        /// </summary>
        [DataMember]
        public string SerialNumber { get; set; }

        /// <summary>
        /// DeviceId
        /// </summary>
        [DataMember]
        public string DeviceId { get; set; }

        /// <summary>
        /// Asset Number
        /// </summary>
        [DataMember]
        public string AssetNumber { get; set; }

        /// <summary>
        /// Last Update in yyyy-MM-dd format
        /// </summary>
        [DataMember]
        public DateTime LastUpdate { get; set; }

        /// <summary>
        /// Counters
        /// </summary>
        [DataMember]
        public IList<CountersCounterDto> Counters { get; set; }
    }
    
    
    /// <summary>
    /// Counter
    /// </summary>
    public class CountersCounterDto
    {
        /// <summary>
        /// Mono counter
        /// </summary>
        [DataMember]
        public int? Mono { get; set; }

        /// <summary>
        /// Color counter
        /// </summary>
        [DataMember]
        public int? Color { get; set; }

        /// <summary>
        /// Mono A3 counter
        /// </summary>
        [DataMember]
        public int? MonoA3 { get; set; }

        /// <summary>
        /// Color A3 counter
        /// </summary>
        [DataMember]
        public int? ColorA3 { get; set; }

        /// <summary>
        /// Fax counter
        /// </summary>
        [DataMember]
        public int? Fax { get; set; }

        /// <summary>
        /// Date received
        /// </summary>
        [DataMember]
        public DateTime Date { get; set; }

        /// <summary>
        /// Gets or sets the mono pages in period.
        /// </summary>
        /// <value>
        /// The mono pages in period.
        /// </value>
        [DataMember]
        public int? MonoPagesInPeriod { get; set; }

        /// <summary>
        /// Gets or sets the color pages in period.
        /// </summary>
        /// <value>
        /// The color pages in period.
        /// </value>
        [DataMember]
        public int? ColorPagesInPeriod { get; set; }
    }


    /// <summary>
    /// Represent a counters blend object
    /// </summary>
    [DataContract]
    public class CountersBlendDeviceDto
    {
        /// <summary>
        /// Constructor
        /// </summary>
        public CountersBlendDeviceDto()
        {
            CountersBlend = new List<CountersBlendCounterDto>();
        }

        /// <summary>
        /// Customer Id
        /// </summary>
        [DataMember]
        public string CustomerId { get; set; }

        /// <summary>
        /// Customer Code
        /// </summary>
        [DataMember]
        public string CustomerCode { get; set; }

        /// <summary>
        /// DeviceId
        /// </summary>
        [DataMember]
        public string DeviceId { get; set; }

        /// <summary>
        /// Serial Number
        /// </summary>
        [DataMember]
        public string SerialNumber { get; set; }

        /// <summary>
        /// Asset Number
        /// </summary>
        [DataMember]
        public string AssetNumber { get; set; }

        /// <summary>
        /// Last Update in yyyy-MM-dd format
        /// </summary>
        [DataMember]
        public DateTime LastUpdate { get; set; }

        /// <summary>
        /// CountersBlend
        /// </summary>
        [DataMember]
        public IList<CountersBlendCounterDto> CountersBlend { get; set; }
    }

    /// <summary>
    /// Counters blend retrieved for the date specified
    /// </summary>
    [DataContract]
    public class CountersBlendCounterDto
    {
        /// <summary>
        /// Date received
        /// </summary>
        [DataMember]
        public DateTime Date { get; set; }

        /// <summary>
        /// Counters blended entries for the date specified
        /// </summary>
        [DataMember]
        public IList<CodeValueDto<string, decimal>> Entries { get; set; }
    }
}
